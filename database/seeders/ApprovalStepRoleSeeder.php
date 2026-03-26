<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalStepRoleSeeder extends Seeder
{

    public function run(): void
    {
        $approvalStepRoles = [
            [
                'approval_step_id' => 1,
                'role_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'approval_step_id' => 1,
                'role_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'approval_step_id' => 1,
                'role_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'approval_step_id' => 2,
                'role_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'approval_step_id' => 3,
                'role_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'approval_step_id' => 4,
                'role_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert ke database
        DB::table('approval_step_roles')->insert($approvalStepRoles);

        // Tampilkan pesan sukses
        $this->command->info('✅ ' . count($approvalStepRoles) . ' approval step roles berhasil ditambahkan!');
    }
}
