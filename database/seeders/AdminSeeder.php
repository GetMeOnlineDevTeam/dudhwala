<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            [
                'first_name' => 'admin',
                'last_name' => 'admin',
                'contact_number' => 'admin@123',
                'role' => 'admin',
                'is_verified' => 1,
                'is_member' => 1,
            ]
        );
    }
}
