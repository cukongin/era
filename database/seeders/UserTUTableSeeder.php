<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTUTableSeeder extends Seeder
{
    public function run()
    {
        // Create Staff TU
        User::updateOrCreate(
            ['email' => 'tu@erapor.com'],
            [
                'name' => 'Staf Tata Usaha',
                'password' => Hash::make('password'),
                'role' => 'staff_tu', // Simple role column
            ]
        );

        $this->command->info('User Staff TU created: tu@erapor.com / password');
    }
}
