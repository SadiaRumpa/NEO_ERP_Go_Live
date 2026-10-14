<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\EnquiryAddress;
use App\Models\Lead;
use App\Models\Fjob;
use App\Models\Quotation;
use App\Models\QuotationSection;
use App\Models\QuotationTemplate;
use App\Models\JobType;
use App\Models\StockWarehouse;
use App\Models\StockCategory;
use App\Models\StockItem;
use App\Models\StockItemVariant;
use App\Models\StockItemRequisition;
use App\Models\StockItemRequisitionDetail;
use App\Models\StockItemMovement;
use App\Models\JobSchedule;
use App\Models\JobScheduleWorker;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PhaseTwoCoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'staff']);

        $this->adminUser = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@neoerp.test',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole('super_admin');

        $this->customer = Customer::create([
            'company_name'      => 'Acme Ltd',
            'contact_firstname' => 'John',
            'contact_lastname'  => 'Doe',
            'contact_email'     => 'john@acme.test',
            'contact_phone'     => '01234567890',
            'contact_mobile'    => '07123456789',
            'status'            => 'active',
        ]);
    }

    public function test_enquiry_status_transition_validation(): void
    {
        $this->actingAs($this->adminUser);

        $enquiry = Enquiry::create([
            'enquiry_name'   => 'Roof Inspection',
            'enquiry'        => 'Inspection required',
            'customer_id'    => $this->customer->id,
            'enquiry_status' => 'new',
        ]);

        // Valid transition: new -> inprogress
        $response = $this->postJson("/enquiry/updatestatus/{$enquiry->id}", [
            'status' => 'inprogress',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('inprogress', $enquiry->fresh()->enquiry_status);

        // Invalid transition: cannot transition archived
        $enquiry->update(['enquiry_status' => 'archived']);
        $badResponse = $this->postJson("/enquiry/updatestatus/{$enquiry->id}", [
            'status' => 'inprogress',
        ]);
        $badResponse->assertStatus(422);
    }

    public function test_lead_status_transition_validation(): void
    {
        $this->actingAs($this->adminUser);

        $lead = Lead::create([
            'enquiry_id'  => 0,
            'lead_name'   => 'Solar Roof Job',
            'customer_id' => $this->customer->id,
            'lead_status' => 'new',
        ]);

        // Valid: new -> inprogress
        $response = $this->postJson("/leads/updatestatus/{$lead->id}", [
            'lead_status' => 'inprogress',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('inprogress', $lead->fresh()->lead_status);

        // Invalid transition from archived
        $lead->update(['lead_status' => 'archived']);
        $badResponse = $this->postJson("/leads/updatestatus/{$lead->id}", [
            'lead_status' => 'new',
        ]);
        $badResponse->assertStatus(422);
    }

    public function test_job_status_transition_validation(): void
    {
        $this->actingAs($this->adminUser);

        $job = Fjob::create([
            'job_name'    => 'Commercial Roofing',
            'slug'        => 'comm-roofing-1',
            'customer_id' => $this->customer->id,
            'enquiry_id'  => 0,
            'lead_id'     => 0,
            'job_status'  => 'draft',
        ]);

        // Valid: draft -> accepted
        $response = $this->postJson("/job/updatestatus/{$job->id}", [
            'job_status' => 'accepted',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('accepted', $job->fresh()->job_status);

        // Invalid from completed -> draft
        $job->update(['job_status' => 'completed']);
        $badResponse = $this->postJson("/job/updatestatus/{$job->id}", [
            'job_status' => 'draft',
        ]);
        $badResponse->assertStatus(422);
    }

    public function test_quotation_status_and_accepted_locks(): void
    {
        $this->actingAs($this->adminUser);

        $lead = Lead::create([
            'enquiry_id'  => 0,
            'lead_name'   => 'Quotation Test Lead',
            'customer_id' => $this->customer->id,
            'lead_status' => 'new',
        ]);

        $jobType = JobType::create(['name' => 'General Roofing']);
        $template = QuotationTemplate::create([
            'job_type_id'   => $jobType->id,
            'job_type_name' => 'General Roofing',
            'template_name' => 'Standard Roofing Template',
            'status'        => 'active',
        ]);

        $quotation = Quotation::create([
            'lead_id'               => $lead->id,
            'customer_id'           => $this->customer->id,
            'job_type_id'           => $jobType->id,
            'quotation_template_id' => $template->id,
            'quotation_version'     => 'Q-001',
            'total_amount'          => 5000.00,
            'status'                => 'pending',
        ]);

        $section = QuotationSection::create([
            'quotation_id' => $quotation->id,
            'section_name' => 'Scope of Work',
            'section_type' => 'textbox',
            'order'        => 1,
        ]);

        // Valid transition: pending -> accepted
        $response = $this->postJson("/quotation/updatestatus/{$quotation->id}", [
            'status'  => 'accepted',
            'remarks' => 'Client accepted quote',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('accepted', $quotation->fresh()->status);

        // Attempting to transition out of accepted should be rejected (HTTP 422)
        $badTransition = $this->postJson("/quotation/updatestatus/{$quotation->id}", [
            'status' => 'pending',
        ]);
        $badTransition->assertStatus(422);

        // Modifying section of an accepted quote must be rejected (HTTP 422)
        $updateSectionRes = $this->postJson("/quotationsections/update/{$section->id}", [
            'content' => 'Altered scope',
        ]);
        $updateSectionRes->assertStatus(422);
    }

    public function test_pipeline_conversion_idempotency_and_roofing_flags(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create Enquiry with 4 roofing flags
        $enquiry = Enquiry::create([
            'enquiry_name'        => 'Church Spire Repair',
            'enquiry'             => 'Spire inspection needed',
            'customer_id'         => $this->customer->id,
            'enquiry_status'      => 'new',
            'annual_maintenance' => true,
            'installations'      => false,
            'repairs'            => true,
            'testing'            => true,
        ]);

        EnquiryAddress::create([
            'enquiry_id'        => $enquiry->id,
            'contact_firstname' => 'Jane',
            'contact_lastname'  => 'Smith',
            'contact_email'     => 'jane@church.test',
            'contact_phone'     => '0123456789',
            'address'           => 'High Street 10',
            'postcode'          => 'SW1A 1AA',
        ]);

        // Convert Enquiry to Lead via Action / Controller
        $conv1 = $this->postJson('/enquiry-to-lead-conversion', [
            'enquiry_id' => $enquiry->id,
            'status'     => 'converted_to_lead',
        ]);
        $conv1->assertStatus(200);
        $this->assertEquals('converted_to_lead', $enquiry->fresh()->enquiry_status);

        $lead = Lead::where('enquiry_id', $enquiry->id)->first();
        $this->assertNotNull($lead);
        $this->assertTrue((bool)$lead->annual_maintenance);
        $this->assertTrue((bool)$lead->repairs);
        $this->assertTrue((bool)$lead->testing);
        $this->assertFalse((bool)$lead->installations);

        // Idempotency: second conversion of same enquiry MUST return 409 Conflict
        $conv2 = $this->postJson('/enquiry-to-lead-conversion', [
            'enquiry_id' => $enquiry->id,
            'status'     => 'converted_to_lead',
        ]);
        $conv2->assertStatus(409);

        // Convert Lead to Job
        $convJob1 = $this->postJson('/lead-to-job-conversion', [
            'lead_id' => $lead->id,
            'status'  => 'converted_to_job',
        ]);
        $convJob1->assertStatus(200);
        $this->assertEquals('converted_to_job', $lead->fresh()->lead_status);

        $job = Fjob::where('lead_id', $lead->id)->first();
        $this->assertNotNull($job);
        // Roofing flags must be copied
        $this->assertTrue((bool)$job->annual_maintenance);
        $this->assertTrue((bool)$job->repairs);
        $this->assertTrue((bool)$job->testing);
        $this->assertFalse((bool)$job->installations);

        // Idempotency: second conversion of same lead MUST return 409 Conflict
        $convJob2 = $this->postJson('/lead-to-job-conversion', [
            'lead_id' => $lead->id,
            'status'  => 'converted_to_job',
        ]);
        $convJob2->assertStatus(409);
    }

    public function test_stock_ledger_integrity_and_requisition_workflow(): void
    {
        $this->actingAs($this->adminUser);

        $warehouse = StockWarehouse::create([
            'name'     => 'Central Warehouse',
            'location' => 'London',
        ]);

        $category = StockCategory::create(['name' => 'Roofing Tiles']);
        $item = StockItem::create([
            'category_id' => $category->id,
            'name'        => 'Slate Tile 300x200',
        ]);

        $variant = StockItemVariant::create([
            'stock_item_id' => $item->id,
            'name'          => 'Dark Slate 300x200',
            'sku'           => 'SLATE-300-BLK',
            'avg_price'     => 12.50,
            'quantity'      => 50.0,
            'unit'          => 'pcs',
            'warehouse_id'  => $warehouse->id,
            'is_active'     => true,
        ]);

        $job = Fjob::create([
            'job_name'    => 'Tile Replacement Job',
            'slug'        => 'tile-rep-job',
            'customer_id' => $this->customer->id,
            'enquiry_id'  => 0,
            'lead_id'     => 0,
            'job_status'  => 'accepted',
        ]);

        $requisition = StockItemRequisition::create([
            'requisition_number' => 'REQ-' . Str::upper(Str::random(6)),
            'fjob_id'            => $job->id,
            'stock_warehouse_id' => $warehouse->id,
            'requisition_date'   => now(),
            'status'             => 'draft',
            'requested_by'       => $this->adminUser->id,
        ]);

        // 1. Adding line item must NOT mutate stock
        $this->post('/stockitemrequisitions/store-details', [
            'stock_item_requisition_id' => $requisition->id,
            'stock_item_id'             => "{$variant->id}_{$variant->quantity}_{$variant->unit}",
            'quantity'                  => 20,
            'unit'                      => 'pcs',
        ]);

        $this->assertEquals(50.0, $variant->fresh()->quantity, 'Stock variant quantity must not decrement on draft requisition line addition');
        $this->assertDatabaseCount('stock_item_movements', 0);

        // 2. Approve requisition
        $approveRes = $this->postJson("/stockitemrequisitions/{$requisition->id}/approve");
        $approveRes->assertStatus(200);
        $reqFresh = $requisition->fresh();
        $this->assertEquals('approved', $reqFresh->status);
        $this->assertEquals($this->adminUser->id, $reqFresh->approved_by);
        $this->assertNotNull($reqFresh->approved_at);

        // 3. Issue requisition: decrements stock and writes ledger movement
        $issueRes = $this->postJson("/stockitemrequisitions/{$requisition->id}/issue");
        $issueRes->assertStatus(200);

        $this->assertEquals(30.0, $variant->fresh()->quantity, 'Stock variant must decrement by 20 upon issue');
        $this->assertEquals('issued', $requisition->fresh()->status);
        $this->assertEquals($this->adminUser->id, $requisition->fresh()->issued_by);
        $this->assertNotNull($requisition->fresh()->issued_at);

        // Ledger check
        $movement = StockItemMovement::where('item_variant_id', $variant->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('out', $movement->movement_type);
        $this->assertEquals(20.0, $movement->quantity);
        $this->assertEquals($requisition->requisition_number, $movement->reference);

        // 4. Negative stock guard test
        $reqOver = StockItemRequisition::create([
            'requisition_number' => 'REQ-OVER-' . Str::upper(Str::random(4)),
            'fjob_id'            => $job->id,
            'stock_warehouse_id' => $warehouse->id,
            'requisition_date'   => now(),
            'status'             => 'approved',
            'requested_by'       => $this->adminUser->id,
        ]);

        StockItemRequisitionDetail::create([
            'stock_item_requisition_id' => $reqOver->id,
            'stock_item_variant_id'     => $variant->id,
            'quantity'                  => 500, // exceeds available 30
            'unit'                      => 'pcs',
        ]);

        $overIssueRes = $this->postJson("/stockitemrequisitions/{$reqOver->id}/issue");
        $overIssueRes->assertStatus(422);
        $this->assertEquals(30.0, $variant->fresh()->quantity, 'Stock should remain 30 when negative stock guard triggers');
    }

    public function test_scheduler_double_booking_conflict_detection_and_promotion(): void
    {
        $this->actingAs($this->adminUser);

        $worker = User::create([
            'name'     => 'Worker Bob',
            'email'    => 'bob@neoerp.test',
            'password' => bcrypt('password'),
        ]);
        $worker->assignRole('staff');

        $job = Fjob::create([
            'job_name'    => 'Scheduled Roof Job',
            'slug'        => 'sched-roof-1',
            'customer_id' => $this->customer->id,
            'enquiry_id'  => 0,
            'lead_id'     => 0,
            'job_status'  => 'accepted',
        ]);

        // Schedule worker for 2026-04-01 to 2026-04-05
        $sched1 = $this->postJson('/scheduler/store-job-schedule', [
            'job_id'         => $job->id,
            'daterange'      => '2026-04-01 to 2026-04-05',
            'workers'        => [$worker->id],
            'instructions'   => 'Check roof slates',
            'is_provisional' => true,
        ]);
        $sched1->assertStatus(201);
        $this->assertFalse($sched1->json('has_conflict'));
        $scheduleId = $sched1->json('job_schedule.id');

        // Second booking overlapping for the same worker: 2026-04-03 to 2026-04-07
        $sched2 = $this->postJson('/scheduler/store-job-schedule', [
            'job_id'       => $job->id,
            'daterange'    => '2026-04-03 to 2026-04-07',
            'workers'      => [$worker->id],
            'instructions' => 'Gutter maintenance',
        ]);
        $sched2->assertStatus(201);
        $this->assertTrue($sched2->json('has_conflict'), 'Should flag conflict when worker is double booked on overlapping dates');

        // Test provisional schedule promotion
        $promoteRes = $this->postJson("/scheduler/promote-provisional/{$scheduleId}");
        $promoteRes->assertStatus(200);
        $this->assertFalse((bool)JobSchedule::find($scheduleId)->is_provisional);
    }

    public function test_customer_deduplication(): void
    {
        $this->actingAs($this->adminUser);

        // Attempting to create duplicate customer with same company name
        $response = $this->post('/customers', [
            'companyName'            => 'Acme Ltd', // already created in setUp
            'contactPersonFirstName' => 'Other',
            'contactPhone'           => '0987654321',
            'contactEmail'           => 'other@example.com',
            'contactMobile'          => '07987654321',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Customer::where('company_name', 'Acme Ltd')->count());
    }

    public function test_open_relay_email_protection(): void
    {
        $this->actingAs($this->adminUser);
        Mail::fake();

        $enquiry = Enquiry::create([
            'enquiry_name'   => 'Relay Test Enquiry',
            'enquiry'        => 'Test',
            'customer_id'    => $this->customer->id,
            'enquiry_status' => 'new',
        ]);

        // Attacker attempts to send rejection mail to arbitrary unauthorized victim
        $response = $this->postJson("/enquiry/updatestatus/{$enquiry->id}", [
            'status'      => 'rejected',
            'sendtoemail' => 'victim@unauthorized-domain.com',
            'mailsubject' => "Spam\r\nBcc: evil@hacker.com",
            'mailmessage' => 'Spam content',
        ]);

        $response->assertStatus(422);
        Mail::assertNothingQueued();
    }
}
