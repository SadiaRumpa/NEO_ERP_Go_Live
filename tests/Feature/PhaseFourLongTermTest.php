<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Fjob;
use App\Models\Invoice;
use App\Models\JobSafetyChecklist;
use App\Models\JobSchedule;
use App\Models\JobSitePhoto;
use App\Models\JobSiteVisit;
use App\Models\User;
use App\Services\Accounting\AccountingSyncManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseFourLongTermTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $staffUser;
    protected BusinessInfo $businessOne;
    protected BusinessInfo $businessTwo;
    protected Customer $customerOne;
    protected Fjob $jobOne;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $this->businessOne = BusinessInfo::create([
            'business_name'   => 'Apex Industrial Roofing North',
            'business_status' => 'Active',
            'business_email'  => 'north@apexroofing.co.uk',
        ]);

        $this->businessTwo = BusinessInfo::create([
            'business_name'   => 'Apex Industrial Roofing South',
            'business_status' => 'Active',
            'business_email'  => 'south@apexroofing.co.uk',
        ]);

        $this->adminUser = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@neoerp.local',
            'password' => Hash::make('secret123'),
        ]);
        $this->adminUser->assignRole('super_admin');

        $this->staffUser = User::create([
            'name'        => 'Field Engineer John',
            'email'       => 'john@neoerp.local',
            'password'    => Hash::make('secret123'),
            'business_id' => $this->businessOne->id,
        ]);
        $this->staffUser->assignRole('staff');

        $this->customerOne = Customer::create([
            'business_id'       => $this->businessOne->id,
            'company_name'      => 'Manchester Logistics Hub Ltd',
            'contact_firstname' => 'Robert',
            'contact_lastname'  => 'Taylor',
            'contact_email'     => 'robert@manchesterlogistics.co.uk',
            'contact_phone'     => '+44 161 946 0123',
            'status'            => 'active',
        ]);

        $enquiry = Enquiry::create([
            'business_id'        => $this->businessOne->id,
            'customer_id'        => $this->customerOne->id,
            'enquiry_name'       => 'Warehouse 4 Enquiry',
            'enquiry'            => 'Warehouse 4 roof inspection',
            'enquiry_status'     => 'new',
            'annual_maintenance' => false,
            'installations'      => true,
            'repairs'            => true,
            'testing'            => false,
        ]);

        $lead = \App\Models\Lead::create([
            'business_id'        => $this->businessOne->id,
            'enquiry_id'         => $enquiry->id,
            'customer_id'        => $this->customerOne->id,
            'lead_name'          => 'Warehouse 4 Lead',
            'enquiry'            => 'Warehouse 4 roof inspection',
            'lead_status'        => 'new',
            'annual_maintenance' => false,
            'installations'      => true,
            'repairs'            => true,
            'testing'            => false,
        ]);

        $this->jobOne = Fjob::create([
            'business_id'        => $this->businessOne->id,
            'enquiry_id'         => $enquiry->id,
            'lead_id'            => $lead->id,
            'customer_id'        => $this->customerOne->id,
            'job_name'           => 'Warehouse 4 Flat Roof Overlay',
            'slug'               => 'warehouse-4-flat-roof-overlay-' . uniqid(),
            'job_status'         => JobStatus::NEW->value,
            'annual_maintenance' => false,
            'installations'      => true,
            'repairs'            => true,
            'testing'            => false,
        ]);

        // Schedule worker for John
        $schedule = JobSchedule::create([
            'job_id'          => $this->jobOne->id,
            'start_date'      => Carbon::today()->format('Y-m-d'),
            'end_date'        => Carbon::today()->addDays(2)->format('Y-m-d'),
            'schedule_status' => 'confirmed',
        ]);
        $schedule->workers()->attach($this->staffUser->id, [
            'assigned_date' => Carbon::today()->format('Y-m-d'),
            'status'        => 'assigned',
        ]);
    }

    public function test_pwa_manifest_and_service_worker_availability(): void
    {
        // 1. Web App Manifest
        $manifestRes = $this->get('/manifest.json');
        $manifestRes->assertStatus(200);
        $manifestRes->assertJsonFragment([
            'name'        => 'NeoERP Field Engineer Portal',
            'short_name'  => 'NeoField',
            'display'     => 'standalone',
        ]);

        // 2. Service Worker File (/sw.js and /service-worker.js alias)
        $swRes = $this->get('/sw.js');
        $swRes->assertStatus(200);
        $this->assertStringContainsString('neoerp-field-v2', $swRes->getContent());

        $swServiceWorkerRes = $this->get('/service-worker.js');
        $swServiceWorkerRes->assertStatus(200);
        $this->assertStringContainsString('neoerp-field-v2', $swServiceWorkerRes->getContent());
    }

    public function test_field_engineer_mobile_workflow_arrival_safety_and_photos(): void
    {
        Storage::fake('public');

        // 1. Staff accesses Field Hub
        $hubRes = $this->actingAs($this->staffUser)->get('/field/jobs');
        $hubRes->assertStatus(200);
        $hubRes->assertSee('Warehouse 4 Flat Roof Overlay');

        // 2. View specific job mobile workspace
        $jobView = $this->actingAs($this->staffUser)->get("/field/jobs/{$this->jobOne->id}");
        $jobView->assertStatus(200);

        // 3. Arrive on site with GPS coordinates
        $arriveRes = $this->actingAs($this->staffUser)->post("/field/jobs/{$this->jobOne->id}/arrive", [
            'latitude'  => 53.483959,
            'longitude' => -2.244644,
        ]);
        $arriveRes->assertRedirect();

        $visit = JobSiteVisit::where('job_id', $this->jobOne->id)
            ->where('user_id', $this->staffUser->id)
            ->first();
        $this->assertNotNull($visit);
        $this->assertEquals('in_progress', $visit->status);
        $this->assertEquals(JobStatus::INPROGRESS->value, $this->jobOne->fresh()->job_status);

        // 4. Submit Site RAMS Safety Checklist
        $safetyRes = $this->actingAs($this->staffUser)->post("/field/jobs/{$this->jobOne->id}/safety", [
            'working_at_height_passed' => 1,
            'ppe_compliant'            => 1,
            'edge_protection_verified' => 1,
            'weather_acceptable'       => 1,
            'risk_assessment_notes'    => 'Parapet anchor points inspected and cleared for work.',
            'signed_by_worker'         => 'John Lead Engineer',
        ]);
        $safetyRes->assertRedirect();

        $checklist = JobSafetyChecklist::where('job_site_visit_id', $visit->id)->first();
        $this->assertNotNull($checklist);
        $this->assertTrue((bool) $checklist->working_at_height_passed);
        $this->assertEquals('John Lead Engineer', $checklist->signed_by_worker);

        // 5. Upload site photo with geolocation
        $fakePhoto = UploadedFile::fake()->image('gutter_crack.jpg', 800, 600);
        $photoRes = $this->actingAs($this->staffUser)->post("/field/jobs/{$this->jobOne->id}/photo", [
            'photo'     => $fakePhoto,
            'caption'   => 'Parapet gutter crack inspection',
            'stage'     => 'during',
            'latitude'  => 53.483959,
            'longitude' => -2.244644,
        ]);
        $photoRes->assertRedirect();

        $this->assertDatabaseHas('job_site_photos', [
            'job_id'  => $this->jobOne->id,
            'user_id' => $this->staffUser->id,
            'stage'   => 'during',
            'caption' => 'Parapet gutter crack inspection',
        ]);

        // 6. Complete site visit with customer electronic signature
        $dummySignatureDataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
        $completeRes = $this->actingAs($this->staffUser)->post("/field/jobs/{$this->jobOne->id}/complete", [
            'customer_signature'   => $dummySignatureDataUri,
            'customer_signer_name' => 'Robert Taylor (Site Facilities Manager)',
            'notes'                => 'All roof overlay membranes properly sealed and inspected.',
            'mark_job_completed'   => 1,
        ]);
        $completeRes->assertRedirect(route('field.jobs.index'));

        $this->assertEquals('completed', $visit->fresh()->status);
        $this->assertEquals(JobStatus::COMPLETED->value, $this->jobOne->fresh()->job_status);
        $this->assertNotNull($visit->fresh()->signed_at);
    }

    public function test_invoice_generation_and_uk_cis_vat_calculation(): void
    {
        // 1. Generate Invoice from Job
        $genRes = $this->actingAs($this->adminUser)->post("/invoices/{$this->jobOne->id}/generate-from-job");
        $genRes->assertRedirect();

        $invoice = Invoice::where('job_id', $this->jobOne->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($this->customerOne->id, $invoice->customer_id);

        // Subtotal = 3500 (labor) + 1850 (materials) = 5350
        $this->assertEquals('5350.00', number_format($invoice->subtotal, 2, '.', ''));

        // 20% VAT on 5350 = 1070
        $this->assertEquals('1070.00', number_format($invoice->tax_amount, 2, '.', ''));

        // Total gross = 6420
        $this->assertEquals('6420.00', number_format($invoice->total_amount, 2, '.', ''));

        // 20% CIS deduction on 3500 qualifying labor = 700
        $this->assertEquals('700.00', number_format($invoice->cis_deduction_amount, 2, '.', ''));

        // Net payable = 6420 - 700 = 5720
        $this->assertEquals('5720.00', number_format($invoice->net_payable, 2, '.', ''));

        // 2. Download Invoice PDF
        $pdfRes = $this->actingAs($this->adminUser)->get("/invoices/{$invoice->id}/pdf");
        $pdfRes->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfRes->headers->get('content-type'));

        // 3. Record Payment
        $payRes = $this->actingAs($this->adminUser)->post("/invoices/{$invoice->id}/record-payment", [
            'payment_reference' => 'BACS-MANCH-9021',
            'paid_at'           => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        $payRes->assertRedirect();
        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertEquals('BACS-MANCH-9021', $invoice->fresh()->payment_reference);
    }

    public function test_accounting_integration_xero_and_quickbooks_sync(): void
    {
        // Generate an invoice
        $this->actingAs($this->adminUser)->post("/invoices/{$this->jobOne->id}/generate-from-job");
        $invoice = Invoice::where('job_id', $this->jobOne->id)->first();

        // 1. Sync to Xero
        $xeroRes = $this->actingAs($this->adminUser)->post("/invoices/{$invoice->id}/sync", [
            'provider' => 'xero',
        ]);
        $xeroRes->assertRedirect();
        $xeroRes->assertSessionHas('success');

        $this->assertDatabaseHas('accounting_sync_logs', [
            'syncable_type' => Invoice::class,
            'syncable_id'   => $invoice->id,
            'provider'      => 'xero',
            'status'        => 'synced',
        ]);

        // 2. Sync to QuickBooks
        $qbRes = $this->actingAs($this->adminUser)->post("/invoices/{$invoice->id}/sync", [
            'provider' => 'quickbooks',
        ]);
        $qbRes->assertRedirect();
        $qbRes->assertSessionHas('success');

        $this->assertDatabaseHas('accounting_sync_logs', [
            'syncable_type' => Invoice::class,
            'syncable_id'   => $invoice->id,
            'provider'      => 'quickbooks',
            'status'        => 'synced',
        ]);
    }

    public function test_multi_tenancy_data_isolation_between_branches(): void
    {
        // Branch 2 user
        $southUser = User::create([
            'name'        => 'South Staff User',
            'email'       => 'south@neoerp.local',
            'password'    => Hash::make('secret123'),
            'business_id' => $this->businessTwo->id,
        ]);
        $southUser->assignRole('staff');

        // Customer in Branch 2
        $customerTwo = Customer::create([
            'business_id'       => $this->businessTwo->id,
            'company_name'      => 'Southampton Docks Warehousing Ltd',
            'contact_firstname' => 'Simon',
            'contact_lastname'  => 'Cross',
            'contact_email'     => 'simon@southamptondocks.co.uk',
            'status'            => 'active',
        ]);

        // When Branch 2 user queries customers, they must ONLY see Branch 2 customers
        $this->actingAs($southUser);
        $customersList = Customer::all();

        $this->assertFalse($customersList->contains('id', $this->customerOne->id), 'Branch 2 user should NOT see Branch 1 customer.');
        $this->assertTrue($customersList->contains('id', $customerTwo->id), 'Branch 2 user SHOULD see Branch 2 customer.');

        // When Branch 1 user queries customers, they must ONLY see Branch 1 customers
        $this->actingAs($this->staffUser);
        $customersListOne = Customer::all();

        $this->assertTrue($customersListOne->contains('id', $this->customerOne->id), 'Branch 1 user SHOULD see Branch 1 customer.');
        $this->assertFalse($customersListOne->contains('id', $customerTwo->id), 'Branch 1 user should NOT see Branch 2 customer.');

        // Super Admin switches tenant context to Business 2
        $switchRes = $this->actingAs($this->adminUser)->post('/tenants/switch', [
            'business_id' => $this->businessTwo->id,
        ]);
        $switchRes->assertRedirect();
        $this->assertEquals($this->businessTwo->id, session('current_business_id'));

        // Scoped to Business 2 in session
        $scopedList = Customer::all();
        $this->assertTrue($scopedList->contains('id', $customerTwo->id));
        $this->assertFalse($scopedList->contains('id', $this->customerOne->id));
    }
}
