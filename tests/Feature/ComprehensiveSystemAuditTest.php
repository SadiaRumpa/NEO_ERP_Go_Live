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
}
