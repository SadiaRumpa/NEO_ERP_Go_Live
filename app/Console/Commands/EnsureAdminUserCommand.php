<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\BusinessInfo;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EnsureAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:ensure-admin 
                            {--email=admin@neoerp.local : Email address for the admin account} 
                            {--password=NeoErp!Admin2026#Secure : Password to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure super admin user exists with active credentials and full permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $business = BusinessInfo::first();
        if (!$business) {
            $business = BusinessInfo::create([
                'business_name' => 'Neoteric Construction Ltd',
                'business_email' => $email,
                'business_status' => 'Active',
            ]);
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'business_id' => $business->id,
            ]
        );

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $permissions = Permission::pluck('id', 'id')->all();
        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }
        $user->assignRole([$role->id]);

        $this->info("✓ Super Admin confirmed: {$user->email}");
        $this->info("✓ Password updated to: {$password}");
        $this->info("✓ Role 'super_admin' assigned with " . count($permissions) . " permissions");

        return 0;
    }
}
