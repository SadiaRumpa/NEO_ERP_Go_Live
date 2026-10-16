<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email' => 'admin_test@neoerp.local',
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_rejects_password_shorter_than_8_characters(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/users', [
            'name' => 'Test Short',
            'email' => 'short@neoerp.local',
            'password' => 'ab',
            'confirm-password' => 'ab',
            'roles' => ['staff'],
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', ['email' => 'short@neoerp.local']);
    }

    public function test_rejects_password_without_numbers_or_symbols_or_mixed_case(): void
    {
        // Missing uppercase, number, symbol
        $res1 = $this->actingAs($this->adminUser)->post('/users', [
            'name' => 'Test Weak 1',
            'email' => 'weak1@neoerp.local',
            'password' => 'lowercaseonly',
            'confirm-password' => 'lowercaseonly',
            'roles' => ['staff'],
        ]);
        $res1->assertSessionHasErrors(['password']);

        // Missing symbol and lowercase
        $res2 = $this->actingAs($this->adminUser)->post('/users', [
            'name' => 'Test Weak 2',
            'email' => 'weak2@neoerp.local',
            'password' => 'UPPERCASE123',
            'confirm-password' => 'UPPERCASE123',
            'roles' => ['staff'],
        ]);
        $res2->assertSessionHasErrors(['password']);

        // Missing symbol
        $res3 = $this->actingAs($this->adminUser)->post('/users', [
            'name' => 'Test Weak 3',
            'email' => 'weak3@neoerp.local',
            'password' => 'NoSymbolPass123',
            'confirm-password' => 'NoSymbolPass123',
            'roles' => ['staff'],
        ]);
        $res3->assertSessionHasErrors(['password']);
    }

    public function test_accepts_strong_compliant_password(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/users', [
            'name' => 'Alice Strong',
            'email' => 'alice@neoerp.local',
            'password' => 'SecurePass2026#',
            'confirm-password' => 'SecurePass2026#',
            'roles' => ['staff'],
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'alice@neoerp.local',
            'name' => 'Alice Strong',
        ]);
    }
}
