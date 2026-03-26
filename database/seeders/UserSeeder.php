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
                'department_id' => null,
                'name' => 'SUPER USER',
                'username' => 'su',
                'email' => 'joo@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 2,
                'department_id' => null,
                'name' => 'ADMIN',
                'username' => 'admin',
                'email' => 'hendar_admin@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 7,
                'department_id' => null,
                'name' => 'GENERAL MANAGER',
                'username' => 'gm',
                'email' => 'gm@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 8,
                'department_id' => null,
                'name' => 'MANAGER ACCOUNTING',
                'username' => 'macc',
                'email' => 'm_acc@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 9,
                'department_id' => null,
                'name' => 'FINANCE',
                'username' => 'finace',
                'email' => 'finace@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 3,
                'department_id' => 5,
                'name' => 'HENDAR',
                'username' => 'hendar',
                'email' => 'hendar@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 4,
                'department_id' => 5,
                'name' => 'SUPERVISOR PURCHASING',
                'username' => 'supervisor',
                'email' => 'supervisor_accounting@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 5,
                'department_id' => 5,
                'name' => 'CHEF PURCHASING',
                'username' => 'chef',
                'email' => 'chef_accounting@gmail.com',
                'password' => Hash::make('11111111'),
            ],
            [
                'role_id' => 6,
                'department_id' => 5,
                'name' => 'MANAGER PURCHASING',
                'username' => 'manager',
                'email' => 'manager_accounting@gmail.com',
                'password' => Hash::make('11111111'),
            ],

        ];

        // Insert ke database
        DB::table('users')->insert($user);

        // Tampilkan pesan sukses
        $this->command->info('✅ ' . count($user) . ' user berhasil ditambahkan!');
    }
}
