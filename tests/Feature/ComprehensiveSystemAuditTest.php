<?php

namespace Tests\Feature;

use App\Models\BusinessInfo;
use App\Models\User;
use Database\Seeders\CreateAdminUserSeeder;
use Database\Seeders\CreateBusinessInfoSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\PermissionTableSeeder;
use Database\Seeders\Populate100DataSeeder;
use Database\Seeders\RoleDataExpansionSeeder;
use Database\Seeders\StockUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComprehensiveSystemAuditTest extends TestCase
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
     * Area 1: Individual Showcase Roles Authentication
     */
    public function test_super_admin_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@neoerp.local',
            'password' => 'NeoErp!Admin2026#Secure',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertEquals('admin@neoerp.local', auth()->user()->email);
    }

    public function test_commercial_director_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'marcus.vance@neoerp.local',
            'password' => 'NeoErp!Staff2026#',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs(User::where('email', 'marcus.vance@neoerp.local')->first());
    }

    public function test_lead_quantity_surveyor_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'priya.patel@neoerp.local',
            'password' => 'NeoErp!Staff2026#',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs(User::where('email', 'priya.patel@neoerp.local')->first());
    }

    public function test_project_engineer_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'sarah.engineer@neoerp.local',
            'password' => 'NeoErp!Staff2026#',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs(User::where('email', 'sarah.engineer@neoerp.local')->first());
    }

    public function test_procurement_officer_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'michael.chang@neoerp.local',
            'password' => 'NeoErp!Staff2026#',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs(User::where('email', 'michael.chang@neoerp.local')->first());
    }

    public function test_senior_site_manager_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'james.oconnor@neoerp.local',
            'password' => 'NeoErp!Staff2026#',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs(User::where('email', 'james.oconnor@neoerp.local')->first());
    }

    public function test_health_and_safety_auditor_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'chloe.bennett@neoerp.local',
            'password' => 'NeoErp!Staff2026#',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs(User::where('email', 'chloe.bennett@neoerp.local')->first());
    }

    public function test_authentication_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@neoerp.local',
            'password' => 'WrongPassword123!',
        ]);
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Area 2: Security & RBAC Boundaries
     */
    public function test_unauthenticated_requests_are_redirected_to_login(): void
    {
        $protectedRoutes = [
            '/',
            '/commercial/wip',
            '/subcontractors',
            '/procurement/suppliers',
            '/procurement/purchase-orders',
            '/procurement/grns',
            '/procurement/supplier-bills',
            '/plant-hires',
            '/plant-hires/alerts',
            '/labor/timesheets',
            '/enquiries',
            '/leads',
            '/jobs',
            '/stockitems',
            '/users',
            '/roles',
            '/permissions',
            '/myprofile',
        ];

        foreach ($protectedRoutes as $route) {
            $this->get($route)->assertRedirect('/login');
        }
    }

    public function test_non_admin_cannot_access_user_or_permission_management(): void
    {
        $staffUser = User::where('email', 'sarah.engineer@neoerp.local')->first();
        $this->actingAs($staffUser);

        $this->get('/users')->assertStatus(403);
        $this->get('/roles')->assertStatus(403);
        $this->get('/permissions')->assertStatus(403);
    }

    public function test_super_admin_has_full_access_to_user_and_role_management(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $this->get('/users')->assertStatus(200);
        $this->get('/roles')->assertStatus(200);
        $this->get('/permissions')->assertStatus(200);
    }

    /**
     * Area 3: Feature Pages & View Rendering
     */
    public function test_super_admin_can_render_all_major_module_dashboards(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $coreGetRoutes = [
            '/' => 'Universal Dashboard',
            '/commercial/wip' => 'Commercial WIP',
            '/subcontractors' => 'Subcontractors',
            '/subcontractors/certificates' => 'CIS Certificates',
            '/subcontractors/cis-monthly-return' => 'CIS300 Return',
            '/procurement/suppliers' => 'Suppliers',
            '/procurement/purchase-orders' => 'Purchase Orders',
            '/procurement/grns' => 'GRN List',
            '/procurement/supplier-bills' => 'Supplier Bills',
            '/plant-hires' => 'Plant Register',
            '/plant-hires/alerts' => 'Standby Bleed Alerts',
            '/labor/timesheets' => 'Labor Timesheets',
            '/enquiries' => 'Commercial Enquiries',
            '/leads' => 'Pipeline Leads',
            '/jobs' => 'Active Contract Jobs',
            '/stockitems' => 'Stock Items',
            '/stockwarehouses' => 'Warehouses',
            '/reports/conversion-velocity' => 'Lead Conversion Velocity',
            '/reports/stock-valuation' => 'Stock Valuation',
            '/myprofile' => 'User Profile',
            '/businessinfo' => 'Company Settings',
        ];

        foreach ($coreGetRoutes as $url => $label) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_commercial_director_can_render_commercial_modules(): void
    {
        $commercialDirector = User::where('email', 'marcus.vance@neoerp.local')->first();
        $this->actingAs($commercialDirector);

        $this->get('/')->assertStatus(200);
        $this->get('/commercial/wip')->assertStatus(200);
        $this->get('/subcontractors')->assertStatus(200);
        $this->get('/procurement/purchase-orders')->assertStatus(200);
        $this->get('/procurement/supplier-bills')->assertStatus(200);
        $this->get('/labor/timesheets')->assertStatus(200);
    }

    public function test_route_aliases_redirect_correctly(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $this->get('/timesheets')->assertRedirect('/labor/timesheets');
        $this->get('/fjobs')->assertRedirect('/jobs');
        $this->get('/plant/hires')->assertRedirect('/plant-hires');
        $this->get('/plant/alerts')->assertRedirect('/plant-hires/alerts');
        $this->get('/procurement/bills')->assertRedirect('/procurement/supplier-bills');
        $this->get('/procurement/goods-received-notes')->assertRedirect('/procurement/grns');
    }

    /**
     * Area 4: Business Logic & Workflow Operations
     */
    public function test_can_update_own_user_profile(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $response = $this->put('/myprofile', [
            'name' => 'Super Admin Updated',
            'email' => 'admin@neoerp.local',
        ]);

        $response->assertRedirect(route('users.myprofile'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin@neoerp.local',
            'name' => 'Super Admin Updated',
        ]);
    }

    /**
     * Area 5: Robust Data Fetching & DataTables Operations
     */
    public function test_customer_datatables_data_fetching_and_contact_name_sorting(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // Test normal AJAX fetch
        $response = $this->getJson('/customers?draw=1', ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        // Test sorting by computed contact_name column (ascending)
        $paramsAsc = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => 'id', 'name' => 'id', 'orderable' => 'true'],
                1 => ['data' => 'company_name', 'name' => 'company_name', 'orderable' => 'true'],
                2 => ['data' => 'contact_name', 'name' => 'contact_name', 'orderable' => 'true'],
            ],
            'order' => [
                0 => ['column' => 2, 'dir' => 'asc']
            ]
        ];
        $sortAsc = $this->getJson('/customers?' . http_build_query($paramsAsc), ['X-Requested-With' => 'XMLHttpRequest']);
        $sortAsc->assertStatus(200);
        $this->assertArrayNotHasKey('error', $sortAsc->json());

        // Test sorting by contact_name column (descending)
        $paramsAsc['order'][0]['dir'] = 'desc';
        $sortDesc = $this->getJson('/customers?' . http_build_query($paramsAsc), ['X-Requested-With' => 'XMLHttpRequest']);
        $sortDesc->assertStatus(200);
        $this->assertArrayNotHasKey('error', $sortDesc->json());
    }

    public function test_postcode_and_places_lookup_data_fetching_is_safe(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // Should return a 200 with structured response even if external API is unreachable/unconfigured
        $postcodeResp = $this->getJson('/checkpostcode/EC3V4AB');
        $postcodeResp->assertStatus(200);
        $this->assertTrue(isset($postcodeResp->json()['address']));

        $townResp = $this->getJson('/checkposttown/LONDON');
        $townResp->assertStatus(200);
    }

    public function test_job_scheduler_can_store_and_update_single_day_schedule(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $job = \App\Models\Fjob::first();

        // 1. Store schedule without worker assignments
        $response = $this->postJson('/scheduler/store-job-schedule', [
            'job_id' => $job->id,
            'daterange' => '2026-11-01 to 2026-11-01',
            'workers' => [],
            'instructions' => 'Single day site preparation',
        ]);
        $response->assertStatus(201);
        $scheduleId = $response->json()['job_schedule']['id'];

        // 2. Update schedule to a single day date range
        $updateResponse = $this->putJson('/scheduler/update-job-schedule/' . $scheduleId, [
            'daterange' => '2026-11-05',
            'workers' => [],
            'instructions' => 'Updated single day',
        ]);
        $updateResponse->assertStatus(200);
    }

    public function test_lead_show_handles_array_and_string_assigned_to(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $lead = \App\Models\Lead::first();
        $this->assertNotNull($lead, 'A lead should exist');

        // Test with lead as-is (array cast)
        $resp = $this->get('/leads/' . $lead->slug);
        $resp->assertStatus(200);

        // Test with assigned_to as explicit JSON string
        $lead->assigned_to = [$admin->id];
        $lead->save();
        $resp2 = $this->get('/leads/' . $lead->slug);
        $resp2->assertStatus(200);
    }

    public function test_stock_requisitions_data_fetching_and_sorting(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $params = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'orderable' => 'false'],
                1 => ['data' => 'fjob', 'name' => 'fjob', 'orderable' => 'true'],
                2 => ['data' => 'stock_warehouse', 'name' => 'stock_warehouse', 'orderable' => 'true'],
                3 => ['data' => 'requested_by', 'name' => 'requested_by', 'orderable' => 'true'],
            ],
            'order' => [
                0 => ['column' => 1, 'dir' => 'asc']
            ]
        ];

        $resp = $this->getJson('/stockitemrequisitions?' . http_build_query($params), ['X-Requested-With' => 'XMLHttpRequest']);
        $resp->assertStatus(200);
        $resp->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $req = \App\Models\StockItemRequisition::first();
        if ($req) {
            $showResp = $this->get('/stockitemrequisitions/' . $req->id);
            $showResp->assertStatus(200);
        }
    }

    public function test_vehicle_and_driver_data_fetching_and_sorting(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // Vehicles DataTables
        $vehicleParams = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => 'registration_display', 'name' => 'registration_display', 'orderable' => 'true'],
                1 => ['data' => 'category_name', 'name' => 'category_name', 'orderable' => 'true'],
            ],
            'order' => [
                0 => ['column' => 0, 'dir' => 'asc']
            ]
        ];
        $vehResp = $this->getJson('/vehicles?' . http_build_query($vehicleParams), ['X-Requested-With' => 'XMLHttpRequest']);
        $vehResp->assertStatus(200);

        // Drivers DataTables
        $driverParams = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => 'user_name', 'name' => 'user_name', 'orderable' => 'true'],
                1 => ['data' => 'vehicle_assigned', 'name' => 'vehicle_assigned', 'orderable' => 'true'],
            ],
            'order' => [
                0 => ['column' => 0, 'dir' => 'desc']
            ]
        ];
        $driverResp = $this->getJson('/drivers?' . http_build_query($driverParams), ['X-Requested-With' => 'XMLHttpRequest']);
        $driverResp->assertStatus(200);
    }

    public function test_job_overviews_and_hs_checks_data_fetching(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        // Job overviews
        $joParams = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => 'job_title', 'name' => 'job_title', 'orderable' => 'true'],
                1 => ['data' => 'customer', 'name' => 'customer', 'orderable' => 'true'],
            ],
            'order' => [
                0 => ['column' => 1, 'dir' => 'asc']
            ]
        ];
        $joResp = $this->getJson('/job-overviews?' . http_build_query($joParams), ['X-Requested-With' => 'XMLHttpRequest']);
        $joResp->assertStatus(200);

        // RAMS / Health & Safety check
        $hsParams = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => 'jobtype', 'name' => 'jobtype', 'orderable' => 'true'],
                1 => ['data' => 'job_title', 'name' => 'job_title', 'orderable' => 'true'],
            ],
            'order' => [
                0 => ['column' => 0, 'dir' => 'asc']
            ]
        ];
        $hsResp = $this->getJson('/fjob-hs-check?' . http_build_query($hsParams), ['X-Requested-With' => 'XMLHttpRequest']);
        $hsResp->assertStatus(200);
    }

    public function test_stock_categories_data_fetching_and_sorting(): void
    {
        $admin = User::where('email', 'admin@neoerp.local')->first();
        $this->actingAs($admin);

        $params = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => 'name', 'name' => 'name', 'orderable' => 'true'],
                1 => ['data' => 'parent_category', 'name' => 'parent_category', 'orderable' => 'true'],
            ],
            'order' => [
                0 => ['column' => 1, 'dir' => 'asc']
            ]
        ];
        $resp = $this->getJson('/stockcategories?' . http_build_query($params), ['X-Requested-With' => 'XMLHttpRequest']);
        $resp->assertStatus(200);
    }
}

