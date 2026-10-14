<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Enums\JobStatus;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Fjob;
use App\Models\JobSchedule;
use App\Models\JobScheduleWorker;
use App\Models\JobType;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationTemplate;
use App\Models\StockCategory;
use App\Models\StockItem;
use App\Models\StockItemVariant;
use App\Models\User;
use App\Models\VehicleDetail;
use App\Notifications\ComplianceReminderNotification;
use App\Notifications\QuotationAcceptedNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseThreeExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $staffUser;
    protected Customer $customer;
    protected JobType $jobType;
    protected QuotationTemplate $quotationTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'health_and_safety_manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $this->adminUser = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@neoerp.local',
            'password' => Hash::make('secret123'),
        ]);
        $this->adminUser->assignRole('super_admin');

        $this->staffUser = User::create([
            'name'     => 'Field Staff',
            'email'    => 'staff@neoerp.local',
            'password' => Hash::make('secret123'),
        ]);
        $this->staffUser->assignRole('staff');

        $this->customer = Customer::create([
            'company_name'      => 'Apex Commercial Roofing Ltd',
            'contact_firstname' => 'David',
            'contact_lastname'  => 'Evans',
            'contact_email'     => 'david@apexroofing.co.uk',
            'contact_phone'     => '+44 20 7946 0991',
            'status'            => 'active',
        ]);

        $this->jobType = JobType::create([
            'name' => 'Industrial Cladding',
            'slug' => 'industrial-cladding',
        ]);

        $this->quotationTemplate = QuotationTemplate::create([
            'job_type_id'   => $this->jobType->id,
            'job_type_name' => $this->jobType->name,
            'template_name' => 'Standard Commercial Roof Repair',
            'status'        => 'active',
        ]);
    }

    public function test_sanctum_token_issuance_and_authentication(): void
    {
        // 1. Invalid credentials -> 401
        $res = $this->postJson('/api/v1/auth/token', [
            'email'    => 'admin@neoerp.local',
            'password' => 'wrong-pass',
        ]);
        $res->assertStatus(401);
        $res->assertJson(['success' => false]);

        // 2. Valid credentials -> 200 with Bearer Token
        $res = $this->postJson('/api/v1/auth/token', [
            'email'       => 'admin@neoerp.local',
            'password'    => 'secret123',
            'device_name' => 'integration-test',
        ]);
        $res->assertStatus(200);
        $res->assertJsonStructure([
            'success',
            'data' => [
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email', 'roles'],
            ],
        ]);

        $token = $res->json('data.access_token');
        $this->assertNotEmpty($token);

        // 3. Unauthenticated request to protected route -> 401
        $unauthRes = $this->getJson('/api/v1/auth/me');
        $unauthRes->assertStatus(401);

        // 4. Authenticated request with Bearer token -> 200
        $authRes = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/auth/me');
        $authRes->assertStatus(200);
        $authRes->assertJsonPath('data.email', 'admin@neoerp.local');

        // 5. Revoke token
        $revokeRes = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/auth/revoke');
        $revokeRes->assertStatus(200);

        app('auth')->forgetGuards();

        // 6. Subsequence request with revoked token -> 401
        $subsequentRes = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/auth/me');
        $subsequentRes->assertStatus(401);
    }

    public function test_customers_api_crud_and_deduplication(): void
    {
        $token = $this->adminUser->createToken('test')->plainTextToken;

        // List
        $listRes = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/customers');
        $listRes->assertStatus(200);
        $listRes->assertJsonStructure(['success', 'data', 'pagination']);

        // Create
        $createRes = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/customers', [
            'company_name'  => 'Skyline Industrial',
            'contact_name'  => 'Sara Connor',
            'contact_email' => 'sara@skyline.co.uk',
            'contact_phone' => '07800123456',
        ]);
        $createRes->assertStatus(201);
        $this->assertDatabaseHas('customers', ['company_name' => 'Skyline Industrial']);

        // Deduplication 409
        $dupRes = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/customers', [
            'company_name'  => 'Skyline Industrial',
            'contact_name'  => 'Sara Connor',
            'contact_email' => 'sara@skyline.co.uk',
        ]);
        $dupRes->assertStatus(409);
    }

    public function test_enquiries_and_leads_api_lifecycle(): void
    {
        $token = $this->adminUser->createToken('test')->plainTextToken;

        // Create Enquiry
        $enquiryRes = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/enquiries', [
            'customer_id'        => $this->customer->id,
            'enquiry'            => 'Warehouse roof membrane replacement',
            'installations'      => true,
            'annual_maintenance' => true,
        ]);
        $enquiryRes->assertStatus(201);
        $enquiryId = $enquiryRes->json('data.id');

        // Status transition to inprogress
        $statusRes = $this->withHeader('Authorization', "Bearer {$token}")->patchJson("/api/v1/enquiries/{$enquiryId}/status", [
            'status' => 'inprogress',
        ]);
        $statusRes->assertStatus(200);
        $this->assertEquals('inprogress', Enquiry::find($enquiryId)->enquiry_status);

        // Convert to Lead
        $convertRes = $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/enquiries/{$enquiryId}/convert-to-lead");
        $convertRes->assertStatus(201);
        $leadId = $convertRes->json('data.id');

        // Verify Enquiry is converted and Lead exists
        $this->assertEquals(EnquiryStatus::CONVERTED_TO_LEAD->value, Enquiry::find($enquiryId)->enquiry_status);
        $lead = Lead::find($leadId);
        $this->assertNotNull($lead);
        $this->assertTrue((bool) $lead->installations);

        // Duplicate conversion returns 409 Conflict
        $dupConvert = $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/enquiries/{$enquiryId}/convert-to-lead");
        $dupConvert->assertStatus(409);
    }

    public function test_scheduler_api_conflict_warning(): void
    {
        $token = $this->adminUser->createToken('test')->plainTextToken;

        $job = Fjob::create([
            'customer_id' => $this->customer->id,
            'job_name'    => 'Roof Cladding Job',
            'slug'        => 'roof-clad-job',
            'lead_id'     => 0,
            'enquiry_id'  => 0,
            'job_status'  => JobStatus::NEW->value,
        ]);

        // Create first schedule for staffUser on 2026-11-01 to 2026-11-05
        $sched1 = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/schedules', [
            'job_id'     => $job->id,
            'start_date' => '2026-11-01',
            'end_date'   => '2026-11-05',
            'worker_ids' => [$this->staffUser->id],
        ]);
        $sched1->assertStatus(201);
        $this->assertFalse($sched1->json('data.has_conflict'));

        // Create overlapping schedule for same worker on 2026-11-03 to 2026-11-08
        $sched2 = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/schedules', [
            'job_id'     => $job->id,
            'start_date' => '2026-11-03',
            'end_date'   => '2026-11-08',
            'worker_ids' => [$this->staffUser->id],
        ]);
        $sched2->assertStatus(201);
        $this->assertTrue($sched2->json('data.has_conflict'));
        $this->assertNotEmpty($sched2->json('data.conflicts'));
    }

    public function test_customer_quotation_acceptance_portal_with_signed_url(): void
    {
        $lead = Lead::create([
            'customer_id' => $this->customer->id,
            'lead_name'   => 'Commercial Portal Lead',
            'slug'        => 'comm-portal-lead',
            'enquiry_id'  => 0,
            'lead_status' => LeadStatus::INPROGRESS->value,
        ]);

        $quotation = Quotation::create([
            'lead_id'               => $lead->id,
            'customer_id'           => $this->customer->id,
            'job_type_id'           => $this->jobType->id,
            'quotation_template_id' => $this->quotationTemplate->id,
            'quotation_version'     => '1.0',
            'quotation_date'        => now()->toDateString(),
            'total_amount'          => 4500.00,
            'status'                => QuotationStatus::PENDING->value,
        ]);

        // 1. Unauthorized unsigned access is rejected (403)
        $unsignedRes = $this->get('/portal/quotations/' . $quotation->id);
        $unsignedRes->assertStatus(403);

        // 2. Authorized signed URL succeeds (200)
        $signedUrl = URL::temporarySignedRoute(
            'portal.quotations.show',
            now()->addDays(7),
            ['quotation' => $quotation->id]
        );

        $portalRes = $this->get($signedUrl);
        $portalRes->assertStatus(200);
        $portalRes->assertSee('Quotation #' . $quotation->id);
        $portalRes->assertSee('4,500.00');

        // 3. Customer digitally signs and accepts quotation via portal
        $acceptUrl = URL::temporarySignedRoute(
            'portal.quotations.accept',
            now()->addDays(7),
            ['quotation' => $quotation->id]
        );

        $acceptRes = $this->postJson($acceptUrl, [
            'signer_name'      => 'David Evans',
            'signer_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'terms_accepted'   => '1',
        ]);

        $acceptRes->assertStatus(200);
        $acceptRes->assertJson(['success' => true]);

        $quotation->refresh();
        $this->assertEquals(QuotationStatus::ACCEPTED->value, $quotation->status);
        $this->assertEquals('David Evans', $quotation->signer_name);
        $this->assertNotNull($quotation->signed_at);

        // 4. Quotation lock: duplicate acceptance returns 409 Conflict
        $dupAccept = $this->postJson($acceptUrl, [
            'signer_name'      => 'David Evans',
            'signer_signature' => 'data:image/png;base64,...',
            'terms_accepted'   => '1',
        ]);
        $dupAccept->assertStatus(409);
    }

    public function test_in_app_notifications_and_mark_as_read(): void
    {
        $this->actingAs($this->adminUser);

        // Send a notification to adminUser
        $lead = Lead::create([
            'customer_id' => $this->customer->id,
            'lead_name'   => 'Notification Lead',
            'slug'        => 'notif-lead',
            'enquiry_id'  => 0,
            'lead_status' => LeadStatus::INPROGRESS->value,
        ]);

        $quotation = Quotation::create([
            'lead_id'               => $lead->id,
            'customer_id'           => $this->customer->id,
            'job_type_id'           => $this->jobType->id,
            'quotation_template_id' => $this->quotationTemplate->id,
            'total_amount'          => 2000.00,
            'status'                => 'accepted',
            'signer_name'           => 'Jane Doe',
            'signed_at'             => now(),
        ]);

        $this->adminUser->notify(new QuotationAcceptedNotification($quotation));

        $this->assertEquals(1, $this->adminUser->unreadNotifications()->count());

        // Get notifications index via JSON
        $indexRes = $this->getJson('/notifications');
        $indexRes->assertStatus(200);
        $indexRes->assertJsonPath('unread_count', 1);

        $notificationId = $this->adminUser->unreadNotifications()->first()->id;

        // Mark as read
        $markRes = $this->postJson("/notifications/{$notificationId}/read");
        $markRes->assertStatus(200);
        $markRes->assertJsonPath('unread_count', 0);
        $this->assertEquals(0, $this->adminUser->unreadNotifications()->count());
    }

    public function test_compliance_check_reminders_command(): void
    {
        // Create vehicle with MOT expiring in 10 days
        VehicleDetail::create([
            'registration_no'      => 'ROOF-77',
            'vin_number'           => 'VIN-FORD-TRANSIT-77',
            'model'                => 'Ford Transit Custom',
            'mot_expiry_date'      => Carbon::today()->addDays(10)->toDateString(),
            'tax_expiry_date'      => Carbon::today()->addDays(60)->toDateString(),
            'insurance_expiry_date'=> Carbon::today()->addDays(90)->toDateString(),
            'status'               => 'Available',
        ]);

        $this->artisan('compliance:check-reminders --days=30')
            ->assertExitCode(0);

        // Verify adminUser received a notification
        $this->assertTrue(
            $this->adminUser->notifications()->where('type', ComplianceReminderNotification::class)->exists()
        );
    }

    public function test_job_pack_pdf_download(): void
    {
        $this->actingAs($this->adminUser);

        $job = Fjob::create([
            'customer_id'        => $this->customer->id,
            'job_name'           => 'Job Pack Test Job',
            'slug'               => 'job-pack-test',
            'lead_id'            => 0,
            'enquiry_id'         => 0,
            'job_status'         => JobStatus::NEW->value,
            'annual_maintenance' => true,
            'installations'      => true,
        ]);

        $res = $this->get("/fjobs/{$job->id}/job-pack/pdf");
        $res->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $res->headers->get('Content-Type'));
    }

    public function test_reports_conversion_velocity_and_stock_valuation(): void
    {
        $this->actingAs($this->adminUser);

        // Conversion velocity JSON
        $velRes = $this->getJson('/reports/conversion-velocity');
        $velRes->assertStatus(200);
        $velRes->assertJsonStructure(['success', 'data' => ['enquiry_to_lead_count', 'total_pipeline_avg_days']]);

        // Stock category & variant
        $cat = StockCategory::create(['name' => 'Felt & Bitumen', 'slug' => 'felt-bitumen']);
        $item = StockItem::create([
            'category_id' => $cat->id,
            'name'        => 'Underlay Felt 20m',
        ]);
        StockItemVariant::create([
            'stock_item_id' => $item->id,
            'name'          => 'Standard 20m',
            'sku'           => 'FELT-01',
            'quantity'      => 15,
            'avg_price'     => 42.50,
            'unit'          => 'rolls',
        ]);

        // Stock valuation JSON
        $valRes = $this->getJson('/reports/stock-valuation');
        $valRes->assertStatus(200);
        $valRes->assertJsonStructure(['success', 'data' => ['total_valuation', 'total_quantity', 'category_breakdown']]);
        $this->assertEquals(637.50, $valRes->json('data.total_valuation'));

        // Stock CSV Export
        $exportRes = $this->get('/reports/export/stock');
        $exportRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportRes->headers->get('Content-Type'));
    }
}
