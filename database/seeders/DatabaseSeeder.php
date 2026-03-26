<?php

namespace Database\Seeders;

use App\Models\CashAdvance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 

        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            ApprovalStepSeeder::class,
            ApprovalStepRoleSeeder::class,
        ]);
        // CashAdvance::factory()
        //     ->count(50)
        //     ->create();
    }
}
