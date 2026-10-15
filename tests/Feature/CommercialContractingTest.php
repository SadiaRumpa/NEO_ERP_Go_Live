<?php

namespace Tests\Feature;

use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\Fjob;
use App\Models\JobRetention;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\User;
use App\Services\Commercial\CommercialContractService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialContractingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BusinessInfo $business;
    protected Customer $customer;
    protected Fjob $job;
    protected CommercialContractService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create([
            'email' => 'surveyor@neoerp.local',
        ]);
        $this->admin->assignRole($role);

        $this->business = BusinessInfo::firstOrCreate(
            ['business_name' => 'Neoteric Construction Ltd'],
            ['business_status' => 'Active']
        );

        $this->admin->update(['business_id' => $this->business->id]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'company_name' => 'Canary Wharf Estates Ltd',
            'contact_firstname' => 'Alistair',
            'contact_lastname' => 'Vane',
            'contact_email' => 'alistair@canarywharf.com',
            'contact_phone' => '02071239999',
            'status' => 'active',
        ]);

        $this->job = Fjob::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'enquiry_id' => 0,
            'lead_id' => 0,
            'job_name' => 'Commercial Tower Level 14 Fitout',
            'slug' => 'commercial-tower-l14-' . uniqid(),
            'job_status' => 'draft',
        ]);

        $this->service = app(CommercialContractService::class);
    }

    public function test_can_create_variation_with_markup_calculation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('commercial.variations.store'), [
            'job_id' => $this->job->id,
            'title' => 'Supply & Install Heavy-Duty Acoustic Ceiling Panels',
            'description' => 'Architect variation instruction #04',
            'cost_amount' => 4000.00,
            'markup_percentage' => 25.00,
            // Quoted will be auto-calculated to 4000 * 1.25 = 5000.00
        ]);

        $variation = JobVariation::where('job_id', $this->job->id)->first();
        $this->assertNotNull($variation);
        $response->assertRedirect(route('commercial.variations.show', $variation));

        $this->assertEquals(4000.00, (float)$variation->cost_amount);
        $this->assertEquals(25.00, (float)$variation->markup_percentage);
        $this->assertEquals(5000.00, (float)$variation->quoted_amount);
        $this->assertEquals(1000.00, $variation->margin_amount);
        $this->assertEquals(20.00, $variation->margin_percent);
        $this->assertEquals('draft', $variation->status);
    }

    public function test_can_approve_variation_with_client_esignature(): void
    {
        $variation = JobVariation::create([
            'business_id' => $this->business->id,
            'variation_number' => 'VAR-2026-TEST1',
            'job_id' => $this->job->id,
            'title' => 'Structural Column Fire Encasement',
            'cost_amount' => 2000.00,
            'markup_percentage' => 20.00,
            'quoted_amount' => 2400.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('commercial.variations.approve', $variation), [
            'client_signer_name' => 'Alistair Vane',
            'client_signer_email' => 'alistair@canarywharf.com',
            'signature_data' => 'DATA_URL_ESIGN_RECORD_SVG_CANVAS',
        ]);

        $response->assertRedirect(route('commercial.variations.show', $variation));

        $variation->refresh();
        $this->assertEquals('approved', $variation->status);
        $this->assertEquals('Alistair Vane', $variation->client_signer_name);
        $this->assertEquals('alistair@canarywharf.com', $variation->client_signer_email);
        $this->assertNotNull($variation->signed_at);
        $this->assertEquals($this->admin->id, $variation->approved_by);
    }

    public function test_can_reject_variation_with_reason(): void
    {
        $variation = JobVariation::create([
            'business_id' => $this->business->id,
            'variation_number' => 'VAR-2026-TEST2',
            'job_id' => $this->job->id,
            'title' => 'Upgraded Brass Door Ironmongery',
            'cost_amount' => 800.00,
            'quoted_amount' => 1000.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('commercial.variations.reject', $variation), [
            'rejection_reason' => 'Client elected to retain standard chrome specification',
        ]);

        $response->assertRedirect(route('commercial.variations.show', $variation));

        $variation->refresh();
        $this->assertEquals('rejected', $variation->status);
        $this->assertStringContainsString('retain standard chrome', $variation->rejection_reason);
    }

    public function test_can_create_application_for_payment_with_retention_deduction(): void
    {
        // Work completed: £50,000, Materials on site: £10,000 = Gross £60,000
        // Previous certified: £20,000 => Current period gross: £40,000
        // Retention: 5% of £40,000 = £2,000 => Net Claim: £38,000
        $response = $this->actingAs($this->admin)->post(route('commercial.valuations.store'), [
            'job_id' => $this->job->id,
            'valuation_date' => '2026-10-15',
            'period_end' => '2026-10-15',
            'cumulative_work_value' => 50000.00,
            'materials_on_site_value' => 10000.00,
            'previous_certified_amount' => 20000.00,
            'retention_rate' => 5.00,
            'notes' => 'Application #3 for structural fitout works',
        ]);

        $valuation = JobValuation::where('job_id', $this->job->id)->first();
        $this->assertNotNull($valuation);
        $response->assertRedirect(route('commercial.valuations.show', $valuation));

        $this->assertEquals(60000.00, (float)$valuation->gross_valuation);
        $this->assertEquals(20000.00, (float)$valuation->previous_certified_amount);
        $this->assertEquals(40000.00, (float)$valuation->current_gross_amount);
        $this->assertEquals(2000.00, (float)$valuation->retention_deduction);
        $this->assertEquals(38000.00, (float)$valuation->net_claim_amount);

        // Verify automated creation of JobRetention ledger record
        $retention = JobRetention::where('valuation_id', $valuation->id)->first();
        $this->assertNotNull($retention);
        $this->assertEquals(2000.00, (float)$retention->total_retained_amount);
        $this->assertEquals(1000.00, (float)$retention->pc_release_amount);
        $this->assertEquals(1000.00, (float)$retention->defects_release_amount);
        $this->assertEquals('held', $retention->status);
        $this->assertEquals(2000.00, $retention->balance_held);
    }

    public function test_can_certify_and_mark_valuation_paid(): void
    {
        $valuation = $this->service->createValuation([
            'job_id' => $this->job->id,
            'cumulative_work_value' => 20000.00,
            'previous_certified_amount' => 0.00,
            'retention_rate' => 3.00, // 3% of £20,000 = £600 => Net £19,400
        ], $this->admin);

        // Certify
        $responseCert = $this->actingAs($this->admin)->post(route('commercial.valuations.certify', $valuation), [
            'certified_amount' => 19400.00,
        ]);
        $responseCert->assertRedirect(route('commercial.valuations.show', $valuation));

        $valuation->refresh();
        $this->assertEquals('certified', $valuation->status);
        $this->assertEquals(19400.00, (float)$valuation->certified_amount);
        $this->assertNotNull($valuation->certified_at);

        // Mark paid
        $responsePaid = $this->actingAs($this->admin)->post(route('commercial.valuations.mark-paid', $valuation));
        $responsePaid->assertRedirect(route('commercial.valuations.show', $valuation));

        $valuation->refresh();
        $this->assertEquals('paid', $valuation->status);
        $this->assertNotNull($valuation->paid_at);
    }

    public function test_can_release_practical_completion_and_defects_retentions(): void
    {
        $valuation = $this->service->createValuation([
            'job_id' => $this->job->id,
            'cumulative_work_value' => 100000.00,
            'previous_certified_amount' => 0.00,
            'retention_rate' => 5.00, // Retention = £5,000 (PC: £2,500, Defects: £2,500)
        ], $this->admin);

        $retention = JobRetention::where('valuation_id', $valuation->id)->first();
        $this->assertNotNull($retention);
        $this->assertEquals(5000.00, $retention->balance_held);

        // 1. Practical Completion Release (50%)
        $responsePc = $this->actingAs($this->admin)->post(route('commercial.retentions.release-pc', $retention));
        $responsePc->assertRedirect(route('commercial.retentions.index'));

        $retention->refresh();
        $this->assertNotNull($retention->pc_released_at);
        $this->assertEquals(2500.00, $retention->balance_held);
        $this->assertEquals('partially_released', $retention->status);

        // 2. Defects Liability Expiry Release (Final 50%)
        $responseDefects = $this->actingAs($this->admin)->post(route('commercial.retentions.release-defects', $retention));
        $responseDefects->assertRedirect(route('commercial.retentions.index'));

        $retention->refresh();
        $this->assertNotNull($retention->defects_released_at);
        $this->assertEquals(0.00, $retention->balance_held);
        $this->assertEquals('fully_released', $retention->status);
    }

    public function test_can_aggregate_job_commercial_summary(): void
    {
        // 2 Approved Variations (£5,000 and £3,000)
        $var1 = $this->service->createVariation([
            'job_id' => $this->job->id,
            'title' => 'Extra 1',
            'cost_amount' => 4000.00,
            'quoted_amount' => 5000.00,
        ], $this->admin);
        $this->service->approveVariation($var1, 'Client Rep', null, null, $this->admin);

        $var2 = $this->service->createVariation([
            'job_id' => $this->job->id,
            'title' => 'Extra 2',
            'cost_amount' => 2500.00,
            'quoted_amount' => 3000.00,
        ], $this->admin);
        $this->service->approveVariation($var2, 'Client Rep', null, null, $this->admin);

        // 1 Valuation certified for £15,000 with £750 retention
        $val = $this->service->createValuation([
            'job_id' => $this->job->id,
            'cumulative_work_value' => 15000.00,
            'previous_certified_amount' => 0.00,
            'retention_rate' => 5.00,
        ], $this->admin);
        $this->service->certifyValuation($val, 14250.00, $this->admin);

        $summary = $this->service->getJobCommercialSummary($this->job->id);

        $this->assertEquals(8000.00, $summary['approved_variations_total']);
        $this->assertEquals(8000.00, $summary['revised_contract_sum']);
        $this->assertEquals(14250.00, $summary['total_certified_net']);
        $this->assertEquals(750.00, $summary['total_retention_deducted']);
        $this->assertEquals(750.00, $summary['retention_held']);
    }
}
