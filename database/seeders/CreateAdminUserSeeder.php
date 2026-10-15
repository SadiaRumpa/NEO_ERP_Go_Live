<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = env('INITIAL_ADMIN_EMAIL', 'admin@neoerp.local');
        $adminPassword = env('INITIAL_ADMIN_PASSWORD', 'NeoErp!Admin2026#Secure');

        $business = \App\Models\BusinessInfo::first();
        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Super Admin',
                'password' => bcrypt($adminPassword),
                'business_id' => $business?->id ?? 1,
            ]
        );
        if (!$user->business_id && $business) {
            $user->update(['business_id' => $business->id]);
        }

        $role = Role::firstOrCreate(['name' => 'super_admin']);

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
