<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TuUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if exists
        if (!User::where('email', 'tu@erapor.com')->exists()) {
            User::create([
                'name' => 'Staff Tata Usaha',
                'email' => 'tu@erapor.com',
                'password' => Hash::make('password'),
                'role' => 'staff_tu',
            ]);
            $this->command->info('User TU created: tu@erapor.com / password');
        } else {
            $this->command->info('User TU already exists.');
        }
    }
}
