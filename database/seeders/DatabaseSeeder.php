<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionTableSeeder::class,
            CreateBusinessInfoSeeder::class,
            CreateAdminUserSeeder::class,
            StockUnitSeeder::class,
            DemoDataSeeder::class,
            Populate100DataSeeder::class,
            RoleDataExpansionSeeder::class,
        ]);
    }
}
