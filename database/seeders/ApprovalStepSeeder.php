<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApprovalStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $approvalSteps = [
            [
                'step_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'step_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'step_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'step_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert ke database
        DB::table('approval_steps')->insert($approvalSteps);

        // Tampilkan pesan sukses
        $this->command->info('✅ ' . count($approvalSteps) . ' approval steps berhasil ditambahkan!');
    }
}
