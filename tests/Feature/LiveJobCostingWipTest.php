<?php

namespace Tests\Feature;

use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\Fjob;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\LaborRateCard;
use App\Models\LaborTimesheet;
use App\Models\PlantHire;
use App\Models\PurchaseOrder;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\User;
use App\Services\Wip\JobCostingWipService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LiveJobCostingWipTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $worker;
    protected BusinessInfo $business;
    protected Customer $customer;
    protected Fjob $job;
    protected Supplier $supplier;
    protected Subcontractor $subcontractor;
    protected JobCostingWipService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create([
            'email' => 'commercialdirector@neoerp.local',
        ]);
        $this->admin->assignRole($role);

        $this->worker = User::factory()->create([
            'name' => 'Bob Builder',
            'email' => 'bob@neoerp.local',
        ]);

        $this->business = BusinessInfo::firstOrCreate(
            ['business_name' => 'Neoteric Construction Ltd'],
            ['business_status' => 'Active']
        );

        $this->admin->update(['business_id' => $this->business->id]);
        $this->worker->update(['business_id' => $this->business->id]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'company_name' => 'Landsec Developments PLC',
            'contact_firstname' => 'Charles',
            'contact_lastname' => 'Hampton',
            'contact_email' => 'charles@landsec.co.uk',
            'contact_phone' => '02078889999',
            'status' => 'active',
        ]);

        $this->job = Fjob::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'enquiry_id' => 0,
            'lead_id' => 0,
            'job_name' => 'Victoria Office Headquarters Fitout',
            'slug' => 'victoria-office-hq-' . uniqid(),
            'job_status' => 'draft',
        ]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Travis Perkins Commercial',
            'code' => 'SUP-TP-01',
            'contact_name' => 'Trade Counter',
            'email' => 'trade@travisperkins.co.uk',
            'payment_terms' => '30 Days Net',
            'status' => 'active',
        ]);

        $this->subcontractor = Subcontractor::create([
            'business_id' => $this->business->id,
            'company_name' => 'Precision Electrical Services Ltd',
            'utr_number' => '1234567890',
            'cis_tax_status' => 'standard', // 20%
            'status' => 'active',
        ]);

        $this->service = app(JobCostingWipService::class);
    }

    public function test_compute_job_wip_aggregates_all_four_cost_pillars(): void
    {
        // 1. Revenue: Approved Variation of £100,000
        JobVariation::create([
            'business_id' => $this->business->id,
            'variation_number' => 'VAR-TEST-1',
            'job_id' => $this->job->id,
            'title' => 'Main Contract Fitout Package',
            'cost_amount' => 60000.00,
            'quoted_amount' => 100000.00,
            'status' => 'approved',
        ]);

        // 2. Pillar 1 (Materials): Purchase Order £15,000 & 3-Way Matched Bill £15,000
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_number' => 'PO-TEST-1',
            'supplier_id' => $this->supplier->id,
            'job_id' => $this->job->id,
            'order_date' => '2026-10-01',
            'subtotal' => 15000.00,
            'tax_amount' => 3000.00,
            'total_amount' => 15000.00,
            'status' => 'received',
        ]);

        SupplierBill::create([
            'business_id' => $this->business->id,
            'bill_number' => 'BILL-TEST-1',
            'internal_reference' => 'INT-BILL-001',
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'bill_date' => '2026-10-05',
            'due_date' => '2026-11-05',
            'subtotal' => 15000.00,
            'tax_amount' => 3000.00,
            'total_amount' => 15000.00,
            'match_status' => 'approved',
        ]);

        // 3. Pillar 2 (Labor): Approved Timesheet (£2,000 direct pay, £2,536 fully burdened)
        LaborTimesheet::create([
            'business_id' => $this->business->id,
            'timesheet_number' => 'TS-TEST-1',
            'user_id' => $this->worker->id,
            'job_id' => $this->job->id,
            'work_date' => '2026-10-10',
            'total_hours' => 80.0,
            'direct_pay_amount' => 2000.00,
            'fully_burdened_cost' => 2536.00,
            'status' => 'approved',
        ]);

        // 4. Pillar 3 (Subcontractors): Certified Certificate £20,000 gross (CIS deduction £4,000)
        SubcontractorPaymentCertificate::create([
            'business_id' => $this->business->id,
            'certificate_number' => 'SPC-TEST-1',
            'subcontractor_id' => $this->subcontractor->id,
            'job_id' => $this->job->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-14',
            'tax_month' => '2026-10',
            'gross_amount' => 20000.00,
            'materials_cost' => 0.00,
            'labor_amount' => 20000.00,
            'cis_tax_rate' => 20.00,
            'cis_deduction_amount' => 4000.00,
            'net_payable' => 16000.00,
            'payment_status' => 'certified',
        ]);

        // 5. Pillar 4 (Plant & Equipment): MEWP Hire £2,464 accrued
        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-TEST-1',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Scissors Lift & Scaffolding',
            'category' => 'mewp',
            'start_date' => Carbon::parse('2026-10-01'),
            'expected_end_date' => Carbon::parse('2026-10-14'),
            'actual_end_date' => Carbon::parse('2026-10-14'),
            'accrued_hire_cost' => 2464.00,
            'status' => 'off_hired',
        ]);

        $wip = $this->service->computeJobWip($this->job->id);

        // Verification of 4 Pillars:
        // Materials: £15,000
        // Labor: £2,536
        // Subs: £20,000
        // Plant: £2,464
        // Total actual costs: £15,000 + £2,536 + £20,000 + £2,464 = £40,000
        $this->assertEquals(15000.00, $wip['pillars']['materials']['actual_cost']);
        $this->assertEquals(2536.00, $wip['pillars']['labor']['burdened_cost']);
        $this->assertEquals(20000.00, $wip['pillars']['subcontractors']['gross_cost']);
        $this->assertEquals(2464.00, $wip['pillars']['plant']['accrued_cost']);
        $this->assertEquals(40000.00, $wip['total_actual_cost']);

        // Revenue: £100,000
        // Live Gross Profit: £100,000 - £40,000 = £60,000
        // Margin: 60.0%
        $this->assertEquals(100000.00, $wip['revised_contract_sum']);
        $this->assertEquals(60000.00, $wip['live_gross_profit']);
        $this->assertEquals(60.00, $wip['live_gross_margin_percent']);
        $this->assertEquals('healthy', $wip['commercial_status']);
    }

    public function test_commercial_health_status_thresholds(): void
    {
        // 1. Healthy: Margin >= 30% (Contract £100k, Cost £40k -> 60%)
        JobVariation::create([
            'business_id' => $this->business->id,
            'variation_number' => 'VAR-HEALTHY',
            'job_id' => $this->job->id,
            'title' => 'Healthy Contract',
            'cost_amount' => 40000.00,
            'quoted_amount' => 100000.00,
            'status' => 'approved',
        ]);

        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-H',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Tools',
            'category' => 'tools',
            'start_date' => Carbon::today(),
            'expected_end_date' => Carbon::today(),
            'accrued_hire_cost' => 40000.00,
            'status' => 'active',
        ]);

        $wip = $this->service->computeJobWip($this->job->id);
        $this->assertEquals('healthy', $wip['commercial_status']);

        // 2. Warning: Margin 20% - 29.9% (Increase cost by £35,000 -> Cost £75k -> Margin 25%)
        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-W',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Heavy Crane',
            'category' => 'mewp',
            'start_date' => Carbon::today(),
            'expected_end_date' => Carbon::today(),
            'accrued_hire_cost' => 35000.00,
            'status' => 'active',
        ]);

        $wipWarning = $this->service->computeJobWip($this->job->id);
        $this->assertEquals(25.00, $wipWarning['live_gross_margin_percent']);
        $this->assertEquals('warning', $wipWarning['commercial_status']);

        // 3. Critical: Margin < 20% (Increase cost by £10,000 -> Cost £85k -> Margin 15%)
        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-C',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Emergency Shoring',
            'category' => 'scaffolding',
            'start_date' => Carbon::today(),
            'expected_end_date' => Carbon::today(),
            'accrued_hire_cost' => 10000.00,
            'status' => 'active',
        ]);

        $wipCritical = $this->service->computeJobWip($this->job->id);
        $this->assertEquals(15.00, $wipCritical['live_gross_margin_percent']);
        $this->assertEquals('critical', $wipCritical['commercial_status']);
    }

    public function test_wip_unbilled_asset_vs_overbilling_liability(): void
    {
        // Cost incurred: £30,000
        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-WIP-1',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Equipment',
            'category' => 'tools',
            'start_date' => Carbon::today(),
            'expected_end_date' => Carbon::today(),
            'accrued_hire_cost' => 30000.00,
            'status' => 'active',
        ]);

        // Scenario A: Certified revenue £20,000 < Cost £30,000 => WIP Asset of £10,000
        JobValuation::create([
            'business_id' => $this->business->id,
            'application_number' => 'APP-WIP-1',
            'job_id' => $this->job->id,
            'valuation_date' => '2026-10-15',
            'period_end' => '2026-10-15',
            'gross_valuation' => 20000.00,
            'net_claim_amount' => 20000.00,
            'certified_amount' => 20000.00,
            'status' => 'certified',
        ]);

        $wipA = $this->service->computeJobWip($this->job->id);
        $this->assertEquals(10000.00, $wipA['wip_balance']);
        $this->assertStringContainsString('Asset', $wipA['wip_type']);

        // Scenario B: Additional certified claim of £20,000 => Total revenue £40,000 > Cost £30,000 => Liability of -£10,000
        JobValuation::create([
            'business_id' => $this->business->id,
            'application_number' => 'APP-WIP-2',
            'job_id' => $this->job->id,
            'valuation_date' => '2026-10-15',
            'period_end' => '2026-10-15',
            'gross_valuation' => 40000.00,
            'net_claim_amount' => 20000.00,
            'certified_amount' => 20000.00,
            'status' => 'certified',
        ]);

        $wipB = $this->service->computeJobWip($this->job->id);
        $this->assertEquals(-10000.00, $wipB['wip_balance']);
        $this->assertStringContainsString('Liability', $wipB['wip_type']);
    }

    public function test_commercial_portfolio_overview_endpoint_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('commercial.wip.portfolio'));
        $response->assertStatus(200);
        $response->assertSee('Live WIP Job Costing', false);
        $response->assertSee('Landsec Developments PLC');
    }

    public function test_individual_job_costing_dashboard_renders_with_four_pillars(): void
    {
        $response = $this->actingAs($this->admin)->get(route('commercial.wip.job', $this->job->id));
        $response->assertStatus(200);
        $response->assertSee('The 4 Cost Ingestion Pillars');
        $response->assertSee('1. Materials');
        $response->assertSee('2. Labor');
        $response->assertSee('3. Subcontractors');
        $response->assertSee('4. Plant Hire');
    }
}
