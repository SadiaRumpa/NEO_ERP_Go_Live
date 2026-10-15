<?php

namespace Tests\Feature;

use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\Fjob;
use App\Models\PlantHire;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Plant\PlantHireService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlantHireEquipmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BusinessInfo $business;
    protected Customer $customer;
    protected Fjob $job;
    protected Supplier $supplier;
    protected PlantHireService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create([
            'email' => 'plantmanager@neoerp.local',
        ]);
        $this->admin->assignRole($role);

        $this->business = BusinessInfo::firstOrCreate(
            ['business_name' => 'Neoteric Construction Ltd'],
            ['business_status' => 'Active']
        );

        $this->admin->update(['business_id' => $this->business->id]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'company_name' => 'Berkeley Homes Group',
            'contact_firstname' => 'Marcus',
            'contact_lastname' => 'Sterling',
            'contact_email' => 'marcus@berkeleyhomes.co.uk',
            'contact_phone' => '02081234567',
            'status' => 'active',
        ]);

        $this->job = Fjob::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'enquiry_id' => 0,
            'lead_id' => 0,
            'job_name' => 'Wimbledon Residential Phase 2',
            'slug' => 'wimbledon-residential-phase-2-' . uniqid(),
            'job_status' => 'draft',
        ]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Nationwide Platforms Access Hire',
            'code' => 'SUP-NW-PLATFORMS',
            'contact_name' => 'Hire Desk',
            'email' => 'hiredesk@nationwideplatforms.co.uk',
            'payment_terms' => '30 Days Net',
            'status' => 'active',
        ]);

        $this->service = app(PlantHireService::class);
    }

    public function test_can_book_plant_hire_record(): void
    {
        $startDate = Carbon::today()->toDateString();
        $expectedEnd = Carbon::today()->addDays(7)->toDateString();

        $response = $this->actingAs($this->admin)->post(route('plant-hires.store'), [
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Genie Z-45/25J Articulated Boom Lift 16m',
            'category' => 'mewp',
            'serial_asset_tag' => 'TAG-BOOM-01',
            'start_date' => $startDate,
            'expected_end_date' => $expectedEnd,
            'daily_rate' => 95.00,
            'weekly_rate' => 380.00,
            'delivery_charge' => 75.00,
            'collection_charge' => 75.00,
            'notes' => 'Ground protection pads required on grass verge.',
        ]);

        $plantHire = PlantHire::where('job_id', $this->job->id)->first();
        $this->assertNotNull($plantHire);
        $response->assertRedirect(route('plant-hires.show', $plantHire));

        $this->assertEquals('active', $plantHire->status);
        $this->assertEquals(95.00, (float)$plantHire->daily_rate);
        $this->assertEquals(380.00, (float)$plantHire->weekly_rate);
        $this->assertEquals(75.00, (float)$plantHire->delivery_charge);
        $this->assertEquals(75.00, (float)$plantHire->collection_charge);
        $this->assertStringStartsWith('PH-', $plantHire->hire_reference);
    }

    public function test_accrued_cost_calculation_daily_rates_and_transport(): void
    {
        // 3-day hire @ £50/day + £40 delivery + £40 collection = £150 + £80 = £230
        $hire = PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-2026-TEST1',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => '1 Tonne Skip Dumper',
            'category' => 'tools',
            'start_date' => Carbon::parse('2026-10-10'),
            'expected_end_date' => Carbon::parse('2026-10-12'),
            'actual_end_date' => Carbon::parse('2026-10-12'), // 3 days inclusive (10, 11, 12)
            'daily_rate' => 50.00,
            'weekly_rate' => 0.00,
            'delivery_charge' => 40.00,
            'collection_charge' => 40.00,
            'status' => 'off_hired',
        ]);

        $cost = $hire->computeAccruedCost();
        $this->assertEquals(230.00, $cost);
    }

    public function test_accrued_cost_calculation_weekly_rate(): void
    {
        // 14 days hire @ £350/week + £100 delivery + £100 collection = 2 * £350 + £200 = £900
        $hire = PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-2026-TEST2',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => '14m Telehandler with Forks',
            'category' => 'telehandler',
            'start_date' => Carbon::parse('2026-10-01'),
            'expected_end_date' => Carbon::parse('2026-10-14'),
            'actual_end_date' => Carbon::parse('2026-10-14'), // 14 days = exactly 2 weeks
            'daily_rate' => 80.00,
            'weekly_rate' => 350.00,
            'delivery_charge' => 100.00,
            'collection_charge' => 100.00,
            'status' => 'off_hired',
        ]);

        $cost = $hire->computeAccruedCost();
        $this->assertEquals(900.00, $cost);
    }

    public function test_can_identify_overdue_and_expiring_hires(): void
    {
        // Overdue hire: expected end was 3 days ago
        $overdueHire = PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-2026-OVERDUE',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Overdue Scaffolding Tower',
            'category' => 'scaffolding',
            'start_date' => Carbon::today()->subDays(10),
            'expected_end_date' => Carbon::today()->subDays(3),
            'daily_rate' => 40.00,
            'status' => 'active',
        ]);

        // Expiring soon: expected end tomorrow
        $expiringHire = PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-2026-EXPIRING',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Expiring 8yd Enclosed Skip',
            'category' => 'skips',
            'start_date' => Carbon::today()->subDays(5),
            'expected_end_date' => Carbon::today()->addDay(),
            'daily_rate' => 20.00,
            'status' => 'active',
        ]);

        $this->assertTrue($overdueHire->isOverdue());
        $this->assertFalse($overdueHire->isExpiringSoon());

        $this->assertFalse($expiringHire->isOverdue());
        $this->assertTrue($expiringHire->isExpiringSoon(2));

        $alerts = $this->service->getExpiringOrOverdueHires($this->business->id);
        $this->assertCount(1, $alerts['overdue']);
        $this->assertCount(1, $alerts['expiring']);
    }

    public function test_can_off_hire_plant_with_supplier_reference(): void
    {
        $hire = PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-2026-ACTIVE01',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Compressor & Breaker Set',
            'category' => 'tools',
            'start_date' => Carbon::today()->subDays(4),
            'expected_end_date' => Carbon::today(),
            'daily_rate' => 35.00,
            'delivery_charge' => 25.00,
            'collection_charge' => 25.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('plant-hires.off-hire', $hire), [
            'off_hire_reference' => 'NW-OFF-558291',
            'actual_end_date' => Carbon::today()->toDateString(),
            'notes' => 'Cleaned down and ready for collection at gate B.',
        ]);

        $response->assertRedirect(route('plant-hires.show', $hire));

        $hire->refresh();
        $this->assertEquals('off_hired', $hire->status);
        $this->assertEquals('NW-OFF-558291', $hire->off_hire_reference);
        $this->assertNotNull($hire->off_hired_at);
        $this->assertEquals($this->admin->id, $hire->off_hired_by);
        // 5 days (subDays(4) to today) @ 35 = 175 + 50 transport = 225.00
        $this->assertEquals(225.00, (float)$hire->accrued_hire_cost);
    }

    public function test_can_aggregate_job_plant_hire_total_for_wip(): void
    {
        // Hire 1
        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-JOB-01',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Plant Item 1',
            'category' => 'tools',
            'start_date' => Carbon::today()->subDays(2),
            'expected_end_date' => Carbon::today(),
            'actual_end_date' => Carbon::today(),
            'accrued_hire_cost' => 300.00,
            'status' => 'off_hired',
        ]);

        // Hire 2
        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-JOB-02',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Plant Item 2',
            'category' => 'mewp',
            'start_date' => Carbon::today()->subDays(1),
            'expected_end_date' => Carbon::today()->addDays(5),
            'accrued_hire_cost' => 450.00,
            'status' => 'active',
        ]);

        $totalWipPlant = $this->service->getJobAccruedPlantCost($this->job->id);
        $this->assertEquals(750.00, $totalWipPlant);
    }

    public function test_alerts_dashboard_renders_successfully(): void
    {
        PlantHire::create([
            'business_id' => $this->business->id,
            'hire_reference' => 'PH-2026-OVERDUE-VIEW',
            'job_id' => $this->job->id,
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Overdue MEWP Boom',
            'category' => 'mewp',
            'start_date' => Carbon::today()->subDays(10),
            'expected_end_date' => Carbon::today()->subDays(2),
            'daily_rate' => 85.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('plant-hires.alerts'));
        $response->assertStatus(200);
        $response->assertSee('Off-Hire Proactive Alerts');
        $response->assertSee('Overdue MEWP Boom');
    }
}
