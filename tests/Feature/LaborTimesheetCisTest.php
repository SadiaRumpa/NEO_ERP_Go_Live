<?php

namespace Tests\Feature;

use App\Models\BusinessInfo;
use App\Models\Fjob;
use App\Models\LaborRateCard;
use App\Models\LaborTimesheet;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\User;
use App\Services\Labor\LaborCisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaborTimesheetCisTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $worker;
    protected BusinessInfo $business;
    protected Fjob $job;
    protected LaborCisService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create([
            'email' => 'admin@neoerp.local',
        ]);
        $this->admin->assignRole($role);

        $this->worker = User::factory()->create([
            'name' => 'Dave Operative',
            'email' => 'dave@neoerp.local',
        ]);

        $this->business = BusinessInfo::firstOrCreate(
            ['business_name' => 'Neoteric Construction Ltd'],
            ['business_status' => 'Active']
        );

        $this->admin->update(['business_id' => $this->business->id]);
        $this->worker->update(['business_id' => $this->business->id]);

        $customer = \App\Models\Customer::create([
            'business_id' => $this->business->id,
            'company_name' => 'Canary Wharf Group',
            'contact_firstname' => 'Estate',
            'contact_lastname' => 'Manager',
            'contact_email' => 'estates@canarywharf.com',
            'contact_phone' => '02079460999',
            'status' => 'active',
        ]);

        $this->job = Fjob::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'enquiry_id' => 0,
            'lead_id' => 0,
            'job_name' => 'Canary Wharf Commercial Fitout',
            'slug' => 'canary-wharf-commercial-fitout-' . uniqid(),
            'job_status' => 'draft',
        ]);

        $this->service = app(LaborCisService::class);
    }

    public function test_can_create_labor_rate_card_and_compute_burdened_rate(): void
    {
        $response = $this->actingAs($this->admin)->post(route('labor.rate-cards.store'), [
            'user_id' => $this->worker->id,
            'base_hourly_rate' => 25.00,
            'overtime_multiplier_1_5' => 1.50,
            'overtime_multiplier_2_0' => 2.00,
            'ni_burden_percent' => 13.80,
            'pension_burden_percent' => 3.00,
            'overhead_burden_percent' => 10.00,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('labor.rate-cards.index'));

        $rateCard = LaborRateCard::where('user_id', $this->worker->id)->first();
        $this->assertNotNull($rateCard);
        $this->assertEquals(26.80, $rateCard->total_burden_percent);
        $this->assertEquals(1.2680, $rateCard->burden_multiplier);
        $this->assertEquals(31.70, $rateCard->burdened_hourly_rate);
    }

    public function test_can_submit_timesheet_with_burdened_job_wip_cost(): void
    {
        LaborRateCard::create([
            'business_id' => $this->business->id,
            'user_id' => $this->worker->id,
            'base_hourly_rate' => 25.00,
            'overtime_multiplier_1_5' => 1.50,
            'overtime_multiplier_2_0' => 2.00,
            'ni_burden_percent' => 13.80,
            'pension_burden_percent' => 3.00,
            'overhead_burden_percent' => 10.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('labor.timesheets.store'), [
            'user_id' => $this->worker->id,
            'job_id' => $this->job->id,
            'work_date' => '2026-10-15',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'travel_hours' => 1.0,
            'standard_hours' => 8.0,
            'overtime_1_5_hours' => 2.0,
            'overtime_2_0_hours' => 1.0,
            'notes' => 'Ground floor HVAC conduit installation',
        ]);

        $timesheet = LaborTimesheet::where('user_id', $this->worker->id)->first();
        $this->assertNotNull($timesheet);
        $response->assertRedirect(route('labor.timesheets.show', $timesheet));

        // Direct pay: (9 hours standard/travel * 25) + (2 hrs OT1.5 * 37.5) + (1 hr OT2.0 * 50) = 225 + 75 + 50 = 350.00
        $this->assertEquals(350.00, (float)$timesheet->direct_pay_amount);
        // Burdened WIP: 350 * 1.2680 = 443.80
        $this->assertEquals(443.80, (float)$timesheet->fully_burdened_cost);
        $this->assertEquals('submitted', $timesheet->status);
    }

    public function test_can_approve_timesheet_and_allocate_to_wip(): void
    {
        $timesheet = LaborTimesheet::create([
            'business_id' => $this->business->id,
            'timesheet_number' => 'TS-2026-0001',
            'user_id' => $this->worker->id,
            'job_id' => $this->job->id,
            'work_date' => '2026-10-15',
            'total_hours' => 8.0,
            'direct_pay_amount' => 200.00,
            'fully_burdened_cost' => 253.60,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->admin)->post(route('labor.timesheets.approve', $timesheet));
        $response->assertRedirect(route('labor.timesheets.show', $timesheet));

        $timesheet->refresh();
        $this->assertEquals('approved', $timesheet->status);
        $this->assertEquals($this->admin->id, $timesheet->approved_by);
        $this->assertNotNull($timesheet->approved_at);
    }

    public function test_can_reject_timesheet_with_reason(): void
    {
        $timesheet = LaborTimesheet::create([
            'business_id' => $this->business->id,
            'timesheet_number' => 'TS-2026-0002',
            'user_id' => $this->worker->id,
            'job_id' => $this->job->id,
            'work_date' => '2026-10-15',
            'total_hours' => 8.0,
            'direct_pay_amount' => 200.00,
            'fully_burdened_cost' => 253.60,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->admin)->post(route('labor.timesheets.reject', $timesheet), [
            'rejection_reason' => 'Duplicate hours logged for site visit',
        ]);
        $response->assertRedirect(route('labor.timesheets.show', $timesheet));

        $timesheet->refresh();
        $this->assertEquals('rejected', $timesheet->status);
        $this->assertStringContainsString('Duplicate hours logged', $timesheet->notes);
    }

    public function test_can_register_subcontractor_with_hmrc_utr(): void
    {
        $response = $this->actingAs($this->admin)->post(route('subcontractors.store'), [
            'company_name' => 'Apex Mechanical Contractors Ltd',
            'contact_name' => 'Sarah Jenkins',
            'email' => 'accounts@apexmechanical.co.uk',
            'phone' => '02079460123',
            'utr_number' => '1234567890',
            'cis_tax_status' => 'standard',
            'cis_verification_number' => 'V123456789',
            'trade_specialism' => 'HVAC & Ductwork',
            'status' => 'active',
        ]);

        $subcontractor = Subcontractor::where('utr_number', '1234567890')->first();
        $this->assertNotNull($subcontractor);
        $response->assertRedirect(route('subcontractors.show', $subcontractor));
        $this->assertEquals(20.00, $subcontractor->cis_tax_rate);
    }

    public function test_subcontractor_certificate_deducts_cis_on_labor_and_exempts_materials(): void
    {
        $subcontractor = Subcontractor::create([
            'business_id' => $this->business->id,
            'company_name' => 'Apex Mechanical Contractors Ltd',
            'utr_number' => '1234567890',
            'cis_tax_status' => 'standard', // 20%
            'status' => 'active',
        ]);

        // Invoice Gross £1,500, Materials £500 => Labor £1,000 => CIS 20% deduction £200 => Net £1,300
        $response = $this->actingAs($this->admin)->post(route('subcontractors.certificates.store'), [
            'subcontractor_id' => $subcontractor->id,
            'job_id' => $this->job->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-14',
            'gross_amount' => 1500.00,
            'materials_cost' => 500.00,
            'notes' => 'First fix pipework installation',
        ]);

        $cert = SubcontractorPaymentCertificate::where('subcontractor_id', $subcontractor->id)->first();
        $this->assertNotNull($cert);
        $response->assertRedirect(route('subcontractors.certificates.show', $cert));

        $this->assertEquals(1500.00, (float)$cert->gross_amount);
        $this->assertEquals(500.00, (float)$cert->materials_cost);
        $this->assertEquals(1000.00, (float)$cert->labor_amount);
        $this->assertEquals(20.00, (float)$cert->cis_tax_rate);
        $this->assertEquals(200.00, (float)$cert->cis_deduction_amount);
        $this->assertEquals(1300.00, (float)$cert->net_payable);
        $this->assertEquals('certified', $cert->payment_status);
    }

    public function test_subcontractor_certificate_with_higher_and_gross_tax_rates(): void
    {
        $higherSub = Subcontractor::create([
            'business_id' => $this->business->id,
            'company_name' => 'Unverified Drylining Ltd',
            'utr_number' => '9876543210',
            'cis_tax_status' => 'higher', // 30%
            'status' => 'active',
        ]);

        $grossSub = Subcontractor::create([
            'business_id' => $this->business->id,
            'company_name' => 'Premier Structural Steel Ltd',
            'utr_number' => '5554443322',
            'cis_tax_status' => 'gross', // 0%
            'status' => 'active',
        ]);

        // Higher rate (30% on £2,000 labor, £0 materials = £600 deduction)
        $certHigher = $this->service->createPaymentCertificate([
            'subcontractor_id' => $higherSub->id,
            'job_id' => $this->job->id,
            'gross_amount' => 2000.00,
            'materials_cost' => 0.00,
        ], $this->admin);

        $this->assertEquals(600.00, (float)$certHigher->cis_deduction_amount);
        $this->assertEquals(1400.00, (float)$certHigher->net_payable);

        // Gross rate (0% on £5,000 labor = £0 deduction)
        $certGross = $this->service->createPaymentCertificate([
            'subcontractor_id' => $grossSub->id,
            'job_id' => $this->job->id,
            'gross_amount' => 5000.00,
            'materials_cost' => 1000.00,
        ], $this->admin);

        $this->assertEquals(0.00, (float)$certGross->cis_deduction_amount);
        $this->assertEquals(5000.00, (float)$certGross->net_payable);
    }

    public function test_can_mark_payment_certificate_paid(): void
    {
        $sub = Subcontractor::create([
            'business_id' => $this->business->id,
            'company_name' => 'Apex Mechanical Contractors Ltd',
            'utr_number' => '1234567890',
            'cis_tax_status' => 'standard',
            'status' => 'active',
        ]);

        $cert = $this->service->createPaymentCertificate([
            'subcontractor_id' => $sub->id,
            'job_id' => $this->job->id,
            'gross_amount' => 1000.00,
            'materials_cost' => 0.00,
        ], $this->admin);

        $response = $this->actingAs($this->admin)->post(route('subcontractors.certificates.mark-paid', $cert));
        $response->assertRedirect(route('subcontractors.certificates.show', $cert));

        $cert->refresh();
        $this->assertEquals('paid', $cert->payment_status);
        $this->assertNotNull($cert->paid_at);
    }

    public function test_can_generate_hmrc_cis300_monthly_return(): void
    {
        $sub1 = Subcontractor::create([
            'business_id' => $this->business->id,
            'company_name' => 'Subcontractor A Ltd',
            'utr_number' => '1111111111',
            'cis_tax_status' => 'standard', // 20%
            'status' => 'active',
        ]);

        $sub2 = Subcontractor::create([
            'business_id' => $this->business->id,
            'company_name' => 'Subcontractor B Ltd',
            'utr_number' => '2222222222',
            'cis_tax_status' => 'higher', // 30%
            'status' => 'active',
        ]);

        // Cert 1: Gross £1,000, Mat £200, Labor £800, Tax £160, Net £840
        $this->service->createPaymentCertificate([
            'subcontractor_id' => $sub1->id,
            'job_id' => $this->job->id,
            'gross_amount' => 1000.00,
            'materials_cost' => 200.00,
            'tax_month' => '2026-10',
        ], $this->admin);

        // Cert 2: Gross £2,000, Mat £500, Labor £1,500, Tax £450, Net £1,550
        $this->service->createPaymentCertificate([
            'subcontractor_id' => $sub2->id,
            'job_id' => $this->job->id,
            'gross_amount' => 2000.00,
            'materials_cost' => 500.00,
            'tax_month' => '2026-10',
        ], $this->admin);

        $return = $this->service->generateCis300MonthlyReturn('2026-10', $this->business->id);

        $this->assertEquals('2026-10', $return['tax_month']);
        $this->assertEquals(3000.00, $return['total_gross']);
        $this->assertEquals(700.00, $return['total_materials']);
        $this->assertEquals(2300.00, $return['total_labor']);
        $this->assertEquals(610.00, $return['total_deductions']);
        $this->assertEquals(2390.00, $return['total_net_paid']);
        $this->assertCount(2, $return['schedule']);

        // Check controller endpoint renders successfully
        $response = $this->actingAs($this->admin)->get(route('subcontractors.cis-monthly-return', ['tax_month' => '2026-10']));
        $response->assertStatus(200);
        $response->assertSee('CIS300 Monthly Return');
        $response->assertSee('Subcontractor A Ltd');
        $response->assertSee('Subcontractor B Ltd');
    }
}
