<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@madrasah.com',
            'password' => Hash::make('password'), // Hash password biar aman boss
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('User Admin berhasil dibuat! Email: admin@madrasah.com, Pass: password');
    }
}
