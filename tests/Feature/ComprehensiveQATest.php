<?php

namespace Tests\Feature;

use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Fjob;
use App\Models\FjobHsCheck;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\LaborRateCard;
use App\Models\LaborTimesheet;
use App\Models\Lead;
use App\Models\PlantHire;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\User;
use App\Models\VehicleDetail;
use Database\Seeders\CreateAdminUserSeeder;
use Database\Seeders\CreateBusinessInfoSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\PermissionTableSeeder;
use Database\Seeders\Populate100DataSeeder;
use Database\Seeders\RoleDataExpansionSeeder;
use Database\Seeders\StockUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveQATest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionTableSeeder::class,
            CreateBusinessInfoSeeder::class,
            CreateAdminUserSeeder::class,
            StockUnitSeeder::class,
            DemoDataSeeder::class,
            Populate100DataSeeder::class,
            RoleDataExpansionSeeder::class,
        ]);
    }

    /**
     * QA-01: Brand Identity & Public Asset Quality Assurance
     */
    public function test_qa_brand_identity_and_assets_integrity(): void
    {
        // 1. Check Login page renders successfully with Neoteric branding
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('neoteric-digital-logo.png');
        $response->assertSee('NEO ERP');
        $response->assertSee('Commercial Contracting', false);

        // 2. Check Static Brand Files exist on disk
        $this->assertFileExists(public_path('assets/img/branding/neoteric-digital-logo.png'));
        $this->assertFileExists(public_path('assets/img/branding/neoteric-icon.png'));
        $this->assertFileExists(public_path('assets/img/favicon/favicon.ico'));
        $this->assertFileExists(public_path('assets/img/favicon/favicon-32x32.png'));
        $this->assertFileExists(public_path('assets/img/favicon/favicon-192.png'));

        // 3. Check CSS has Neoteric Color Palette (#FF3E48 Coral Red, #17B660 Emerald Green)
        $cssContent = file_get_contents(public_path('assets/css/custom.css'));
        $this->assertStringContainsString('#FF3E48', $cssContent);
        $this->assertStringContainsString('#17B660', $cssContent);
        $this->assertStringContainsString('menu-inner-shadow', $cssContent);

        // 4. Check BusinessInfo database values
        $businessInfo = BusinessInfo::first();
        $this->assertNotNull($businessInfo);
        $this->assertEquals('Neoteric Digital', $businessInfo->business_name);
        $this->assertEquals('NEO ERP', $businessInfo->business_brand_app_name);
        $this->assertStringContainsString('neoteric-digital-logo.png', $businessInfo->business_logo);
    }

    /**
     * QA-02: Universal Dashboard & Navigation Quality Assurance
     */
    public function test_qa_universal_dashboard_and_sidemenu(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Neoteric');
        $response->assertSee('DIGITAL');
        $response->assertSee('NEO ERP');
        $response->assertSee('Dashboard');

        $homeResponse = $this->get('/home');
        $homeResponse->assertRedirect('/');
    }

    /**
     * QA-03: Commercial Contracting & WIP Module Quality Assurance
     */
    public function test_qa_commercial_contracting_and_wip_module(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // 1. Commercial WIP Portfolio Overview
        $wipResp = $this->get('/commercial/wip');
        $wipResp->assertStatus(200);
        $wipResp->assertSee('WIP', false);

        // 2. Commercial Variations Index
        $variationsResp = $this->get('/commercial/variations');
        $variationsResp->assertStatus(200);

        // 3. Valuations Index
        $valuationsResp = $this->get('/commercial/valuations');
        $valuationsResp->assertStatus(200);

        // 4. Retentions Index
        $retentionsResp = $this->get('/commercial/retentions');
        $retentionsResp->assertStatus(200);

        // 5. Individual Job Costing WIP Show View
        $job = Fjob::first();
        if ($job) {
            $jobWipResp = $this->get('/commercial/wip/job/' . $job->id);
            $jobWipResp->assertStatus(200);
            $jobWipResp->assertSee('WIP', false);
        }
    }

    /**
     * QA-04: HMRC CIS & Subcontractor Certificates Quality Assurance
     */
    public function test_qa_hmrc_cis_and_subcontractor_workflow(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // 1. Subcontractors list
        $subResp = $this->get('/subcontractors');
        $subResp->assertStatus(200);

        // 2. Subcontractor Certificates list (canonical route)
        $certResp = $this->get('/subcontractors/certificates');
        $certResp->assertStatus(200);

        // 3. HMRC CIS Monthly Return View
        $cisResp = $this->get('/subcontractors/cis-monthly-return');
        $cisResp->assertStatus(200);

        // 4. Subcontractor Certificate Details
        $cert = SubcontractorPaymentCertificate::first();
        if ($cert) {
            $certShow = $this->get('/subcontractors/certificates/' . $cert->id);
            $certShow->assertStatus(200);
        }

        // 5. Test convenient aliases
        $this->get('/labor/certificates')->assertRedirect('/subcontractors/certificates');
        $this->get('/labor/cis-monthly-return')->assertRedirect('/subcontractors/cis-monthly-return');
    }

    /**
     * QA-05: Procurement & 3-Way Matching Quality Assurance
     */
    public function test_qa_procurement_three_way_matching_module(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // 1. Suppliers list
        $suppliersResp = $this->get('/procurement/suppliers');
        $suppliersResp->assertStatus(200);

        // 2. Purchase Orders list
        $poResp = $this->get('/procurement/purchase-orders');
        $poResp->assertStatus(200);

        // 3. Purchase Order Details
        $po = PurchaseOrder::first();
        if ($po) {
            $poShow = $this->get('/procurement/purchase-orders/' . $po->id);
            $poShow->assertStatus(200);
        }

        // 4. Supplier Bills list
        $billsResp = $this->get('/procurement/supplier-bills');
        $billsResp->assertStatus(200);

        // 5. Goods Received Notes list
        $grnResp = $this->get('/procurement/grns');
        $grnResp->assertStatus(200);
    }

    /**
     * QA-06: Plant & Equipment Hire Quality Assurance
     */
    public function test_qa_plant_hire_equipment_module(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // 1. Plant hire list
        $plantResp = $this->get('/plant-hires');
        $plantResp->assertStatus(200);

        // 2. Plant hire alerts dashboard
        $alertsResp = $this->get('/plant-hires/alerts');
        $alertsResp->assertStatus(200);

        // 3. Plant hire details
        $hire = PlantHire::first();
        if ($hire) {
            $hireShow = $this->get('/plant-hires/' . $hire->id);
            $hireShow->assertStatus(200);
        }
    }

    /**
     * QA-07: Labor, Timesheets & Rates Quality Assurance
     */
    public function test_qa_labor_and_timesheets_module(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // 1. Timesheets list
        $tsResp = $this->get('/labor/timesheets');
        $tsResp->assertStatus(200);

        // 2. Labor rate cards list
        $ratesResp = $this->get('/labor/rate-cards');
        $ratesResp->assertStatus(200);

        // 3. Timesheet details
        $ts = LaborTimesheet::first();
        if ($ts) {
            $tsShow = $this->get('/labor/timesheets/' . $ts->id);
            $tsShow->assertStatus(200);
        }
    }

    /**
     * QA-08: Core Pipeline (Customers, Enquiries, Leads, Jobs, Quotes)
     */
    public function test_qa_core_pipeline_lifecycle_views(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // 1. Customers
        $custResp = $this->get('/customers');
        $custResp->assertStatus(200);
        $customer = Customer::first();
        if ($customer) {
            $this->get('/customers/' . $customer->id)->assertStatus(200);
        }

        // 2. Enquiries
        $enqResp = $this->get('/enquiries');
        $enqResp->assertStatus(200);
        $enquiry = Enquiry::first();
        if ($enquiry) {
            $this->get('/enquiries/' . ($enquiry->slug ?: $enquiry->id))->assertStatus(200);
        }

        // 3. Leads
        $leadsResp = $this->get('/leads');
        $leadsResp->assertStatus(200);
        $lead = Lead::first();
        if ($lead) {
            $this->get('/leads/' . ($lead->slug ?: $lead->id))->assertStatus(200);
        }

        // 4. Jobs
        $jobsResp = $this->get('/jobs');
        $jobsResp->assertStatus(200);
        $job = Fjob::first();
        if ($job) {
            $this->get('/jobs/' . $job->id)->assertStatus(200);
        }

        // 5. Quotations
        $quote = Quotation::first();
        if ($quote) {
            $this->get('/quotation/' . $quote->id)->assertStatus(200);
        }
    }

    /**
     * QA-09: Health & Safety, RAMS and Fleet Compliance
     */
    public function test_qa_health_safety_rams_and_fleet(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // 1. RAMS Check List
        $hsListResp = $this->get('/fjob-hs-check');
        $hsListResp->assertStatus(200);

        // 2. RAMS Check Details (verifies zero 500 error on checkup show)
        $hsCheck = FjobHsCheck::first();
        if ($hsCheck) {
            $hsShowResp = $this->get('/fjob-hs-check/' . $hsCheck->id);
            $hsShowResp->assertStatus(200);
        }

        // 3. Fleet Vehicles
        $vehResp = $this->get('/vehicles');
        $vehResp->assertStatus(200);
        $vehicle = VehicleDetail::first();
        if ($vehicle) {
            $this->get('/vehicle/' . $vehicle->id)->assertStatus(200);
        }

        // 4. Drivers
        $driversResp = $this->get('/drivers');
        $driversResp->assertStatus(200);
    }

    /**
     * QA-10: Scheduler & Field Operations
     */
    public function test_qa_scheduler_and_field_operations(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $schedResp = $this->get('/scheduler');
        $schedResp->assertStatus(200);

        $fieldHubResp = $this->get('/field/jobs');
        $fieldHubResp->assertStatus(200);

        // Canonical alias check
        $fieldAlias = $this->get('/field-hub');
        $fieldAlias->assertRedirect('/field/jobs');
    }

    /**
     * QA-11: DataTables AJAX Integrity Across Key Models
     */
    public function test_qa_datatables_ajax_endpoints_integrity(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $endpoints = [
            '/customers',
            '/enquiries',
            '/leads',
            '/jobs',
            '/vehicles',
            '/drivers',
            '/job-overviews',
            '/fjob-hs-check',
            '/stockcategories',
            '/commercial/variations',
            '/commercial/valuations',
            '/commercial/wip',
            '/plant-hires',
            '/procurement/purchase-orders',
            '/procurement/suppliers',
            '/procurement/supplier-bills',
            '/labor/timesheets',
        ];

        foreach ($endpoints as $uri) {
            $params = [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ];
            $response = $this->getJson($uri . '?' . http_build_query($params), [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ]);

            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Endpoint {$uri} returned unexpected HTTP status {$response->status()}"
            );
        }
    }

    /**
     * QA-12: Security RBAC & Unauthorized Access Containment
     */
    public function test_qa_rbac_security_containment(): void
    {
        // 1. Unauthenticated users cannot access administrative or operational routes
        $protectedRoutes = [
            '/',
            '/users',
            '/roles',
            '/commercial/wip',
            '/procurement/purchase-orders',
            '/plant-hires',
            '/labor/timesheets',
            '/scheduler',
        ];

        foreach ($protectedRoutes as $route) {
            $resp = $this->get($route);
            $resp->assertRedirect('/login');
        }

        // 2. Non-admin roles (e.g., Commercial Director) cannot access User Management
        $commercialDirector = User::where('email', 'marcus.vance@neoerp.local')->first();
        $this->actingAs($commercialDirector);

        $userMgmtResp = $this->get('/users');
        $this->assertEquals(403, $userMgmtResp->status());

        $roleMgmtResp = $this->get('/roles');
        $this->assertEquals(403, $roleMgmtResp->status());
    }
}
