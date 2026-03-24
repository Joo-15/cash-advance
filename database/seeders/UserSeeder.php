<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [

            [
                'role_id' => 1,
                'department_id' => 1,
                'name' => 'Julio',
                'username' => 'joo',
                'email' => 'joo@gmail.com',
                'password' => Hash::make('11111111'),
            ],
        ];

        // Insert ke database
        DB::table('users')->insert($user);

        // Tampilkan pesan sukses
        $this->command->info('✅ ' . count($user) . ' user berhasil ditambahkan!');
    }
}
