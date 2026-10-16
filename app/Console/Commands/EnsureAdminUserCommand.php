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
                            {--email=admin@neoerp.local : Email address for the super admin account} 
                            {--password=NeoErp!Admin2026#Secure : Password to set for super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure super admin and all showcase demo users exist with active credentials and roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminEmail = $this->option('email');
        $adminPassword = $this->option('password');

        $business = BusinessInfo::first();
        if (!$business) {
            $business = BusinessInfo::create([
                'business_name' => 'Neoteric Construction Ltd',
                'business_email' => $adminEmail,
                'business_phone' => '+44 (0) 20 7946 0192',
                'business_address' => 'Floor 24, The Leadenhall Building, 122 Leadenhall St, London EC3V 4AB',
                'business_status' => 'Active',
            ]);
        }

        // 1. Ensure all system roles exist
        $roleNames = [
            'super_admin',
            'admin',
            'commercial-director',
            'job-manager',
            'lead-job-manager',
            'accounts-manager',
            'inventory-manager',
            'fleet-manager',
            'health-safety-manager',
            'office-manager',
            'enquiry-manager',
            'lead-manager',
            'staff',
        ];

        $roles = [];
        foreach ($roleNames as $rName) {
            $roles[$rName] = Role::firstOrCreate(['name' => $rName, 'guard_name' => 'web']);
        }

        // 2. Sync permissions for administrative & commercial roles
        $permissions = Permission::pluck('id', 'id')->all();
        if (!empty($permissions)) {
            $roles['super_admin']->syncPermissions($permissions);
            $roles['admin']->syncPermissions($permissions);
            $roles['commercial-director']->syncPermissions($permissions);
        }

        // 3. Ensure Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($adminPassword),
                'business_id' => $business->id,
            ]
        );
        $superAdmin->syncRoles(['super_admin', 'admin']);
        $this->info("✓ Super Admin confirmed: {$superAdmin->email} (Password: {$adminPassword})");

        // 4. Ensure Showcase Team Accounts with Credentials & Roles
        $staffPassword = 'NeoErp!Staff2026#';
        $showcaseAccounts = [
            [
                'name' => 'Marcus Vance (Commercial Director)',
                'email' => 'marcus.vance@neoerp.local',
                'roles' => ['admin', 'commercial-director'],
            ],
            [
                'name' => 'Sarah Campbell (Project Engineer)',
                'email' => 'sarah.engineer@neoerp.local',
                'roles' => ['job-manager'],
            ],
            [
                'name' => 'James O\'Connor (Senior Site Manager)',
                'email' => 'james.oconnor@neoerp.local',
                'roles' => ['lead-job-manager'],
            ],
            [
                'name' => 'Priya Patel (Lead Quantity Surveyor)',
                'email' => 'priya.patel@neoerp.local',
                'roles' => ['accounts-manager'],
            ],
            [
                'name' => 'Michael Chang (Procurement Officer)',
                'email' => 'michael.chang@neoerp.local',
                'roles' => ['inventory-manager'],
            ],
            [
                'name' => 'Chloe Bennett (H&S Compliance Auditor)',
                'email' => 'chloe.bennett@neoerp.local',
                'roles' => ['health-safety-manager'],
            ],
            [
                'name' => 'Dave Mason (Senior Joiner)',
                'email' => 'dave.operative@neoerp.local',
                'roles' => ['staff'],
            ],
            [
                'name' => 'Liam Wright (Master Electrician)',
                'email' => 'liam.wright@neoerp.local',
                'roles' => ['staff'],
            ],
            [
                'name' => 'Elena Rostova (Architectural Fitout Lead)',
                'email' => 'elena.rostova@neoerp.local',
                'roles' => ['staff'],
            ],
            [
                'name' => 'Tom Hardy (Heavy Plant Operator)',
                'email' => 'tom.hardy@neoerp.local',
                'roles' => ['staff'],
            ],
        ];

        foreach ($showcaseAccounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($staffPassword),
                    'business_id' => $business->id,
                ]
            );
            $user->syncRoles($account['roles']);
            $this->info("✓ User: {$user->email} | Roles: [" . implode(', ', $account['roles']) . "]");
        }

        $this->newLine();
        $this->info("✓ All showcase evaluator accounts verified and permissions synchronized successfully.");

        return 0;
    }
}
