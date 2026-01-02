<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder untuk Administrator
        DB::table('users')->insert([
            'nama_lengkap' => 'Admin Utama',
            'email' => 'admin@mail.id',
            'password' => Hash::make('password123'),
            'jabatan' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seeder untuk Sales
        DB::table('users')->insert([
            'nama_lengkap' => 'Sistem',
            'email' => 'sales@mail.id',
            'password' => Hash::make('password123'),
            'jabatan' => 'Sales',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seeder untuk Teknisi
        DB::table('users')->insert([
            'nama_lengkap' => 'Sistem',
            'email' => 'teknisi@mail.id',
            'password' => Hash::make('password123'),
            'jabatan' => 'Teknisi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // // Seeder untuk Teknisi
        // DB::table('users')->insert([
        //     'nama_lengkap' => 'Teknisi Handal 2',
        //     'email' => 'teknisi2@mail.id',
        //     'password' => Hash::make('password123'),
        //     'jabatan' => 'Teknisi',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // // Seeder untuk Teknisi
        // DB::table('users')->insert([
        //     'nama_lengkap' => 'Teknisi Handal 3',
        //     'email' => 'teknisi3@mail.id',
        //     'password' => Hash::make('password123'),
        //     'jabatan' => 'Teknisi',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }
}
