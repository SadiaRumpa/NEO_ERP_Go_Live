<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Route;

class PhaseOneSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles needed for authorization tests
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'staff']);
    }

    public function test_impersonation_route_is_removed_and_returns_404(): void
    {
        $response1 = $this->get('/login-as-user');
        $response1->assertStatus(404);

        $response2 = $this->get('/login-as-user/1');
        $response2->assertStatus(404);

        $response3 = $this->get('/query-test');
        $response3->assertStatus(404);
    }

    public function test_registration_is_disabled(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(404);

        $postResponse = $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'attacker@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $postResponse->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->get('/users')->assertRedirect('/login');
        $this->get('/roles')->assertRedirect('/login');
        $this->get('/permissions')->assertRedirect('/login');
        $this->get('/enquiries')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_user_or_role_management(): void
    {
        $staffUser = User::factory()->create();
        $staffUser->assignRole('staff');

        $this->actingAs($staffUser)->get('/users')->assertStatus(403);
        $this->actingAs($staffUser)->get('/roles')->assertStatus(403);
        $this->actingAs($staffUser)->get('/permissions')->assertStatus(403);
    }

    public function test_non_admin_cannot_grant_super_admin_role_via_users_update(): void
    {
        $staffUser = User::factory()->create();
        $staffUser->assignRole('staff');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('staff');

        $response = $this->actingAs($staffUser)->put('/users/' . $targetUser->id, [
            'name' => 'Hacked Name',
            'email' => $targetUser->email,
            'roles' => ['super_admin']
        ]);

        $response->assertStatus(403);
        $this->assertFalse($targetUser->fresh()->hasRole('super_admin'));
    }

    public function test_user_can_update_own_profile_via_myprofile(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@test.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->actingAs($user)->put('/myprofile', [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);

        $response->assertRedirect(route('users.myprofile'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);
    }

    public function test_cannot_delete_last_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->delete('/users/' . $admin->id);
        
        // Cannot delete self or last super admin
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_all_registered_controller_methods_exist(): void
    {
        $routes = Route::getRoutes()->getRoutes();
        $missingTargets = [];

        foreach ($routes as $route) {
            $action = $route->getAction();

            if (isset($action['controller'])) {
                $controllerAction = $action['controller'];

                if (str_contains($controllerAction, '@')) {
                    [$controller, $method] = explode('@', $controllerAction);

                    if (!class_exists($controller)) {
                        $missingTargets[] = "Controller class {$controller} does not exist for URI: {$route->uri()}";
                    } elseif (!method_exists($controller, $method)) {
                        $missingTargets[] = "Method {$method} does not exist on {$controller} for URI: {$route->uri()}";
                    }
                }
            }
        }

        $this->assertEmpty($missingTargets, "Found routed targets that do not exist:\n" . implode("\n", $missingTargets));
    }
}
