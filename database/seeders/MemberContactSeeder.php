<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemberContact;

class MemberContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear out old data
        MemberContact::truncate();

        $contacts = [
            // Only one active email
            [
                'name'         => 'Alice Johnson',
                'contact_type' => 'email',
                'contact'      => 'alice.johnson@example.com',
                'is_active'    => true,
            ],
            [
                'name'         => 'Charlie Lee',
                'contact_type' => 'email',
                'contact'      => 'charlie.lee@example.com',
                'is_active'    => false,
            ],
            [
                'name'         => 'Eve Kumar',
                'contact_type' => 'email',
                'contact'      => 'eve.kumar@example.com',
                'is_active'    => false,
            ],

            // Only one active phone
            [
                'name'         => 'Bob Smith',
                'contact_type' => 'phone',
                'contact'      => '+91 98765 43210',
                'is_active'    => true,
            ],
            [
                'name'         => 'David Patel',
                'contact_type' => 'phone',
                'contact'      => '+91 91234 56789',
                'is_active'    => false,
            ],
        ];

        foreach ($contacts as $data) {
            MemberContact::create($data);
        }
    }
}
