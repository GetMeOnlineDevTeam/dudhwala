<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactRequest;

class ContactRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optional: clear out old data
        ContactRequest::truncate();

        $requests = [
            [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'phone_no'   => '+91 98765 43210',
                'subject'    => 'Booking Inquiry',
                'message'    => 'Hi, I would like to check availability for your main hall next weekend.',
            ],
            [
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
                'phone_no'   => '+91 91234 56780',
                'subject'    => 'Pricing Details',
                'message'    => 'Could you please send me the rate card for corporate events?',
            ],
            [
                'first_name' => 'Ali',
                'last_name'  => 'Khan',
                'phone_no'   => '+91 92345 67890',
                'subject'    => 'Catering Options',
                'message'    => 'Do you offer in‑house catering, and if so, what are the menu options?',
            ],
        ];

        foreach ($requests as $data) {
            ContactRequest::create($data);
        }
    }
}
