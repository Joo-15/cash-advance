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
                'role_id' => 1,
                'step_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insert ke database
        DB::table('approval_steps')->insert($approvalSteps);

        // Tampilkan pesan sukses
        $this->command->info('✅ ' . count($approvalSteps) . ' approval steps berhasil ditambahkan!');
    }
}
