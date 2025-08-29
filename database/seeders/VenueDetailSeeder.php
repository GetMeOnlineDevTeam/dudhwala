<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VenueDetail;

class VenueDetailSeeder extends Seeder
{
    public function run(): void
    {
        VenueDetail::create([
            'name' => ' Panigate Hall',
            'about' => 'A spacious and well-ventilated hall suitable for weddings and parties.',
            'amenities' => 'Parking, Single Floor, Lighting, Chairs, Tables',
            'multi_floor' => false,
            'total_floor' => 1,
        ]);

        VenueDetail::create([
            'name' => 'Nagarwada Hall',
            'about' => 'Perfect for community events and small gatherings.',
            'amenities' => 'Multifloor, Garden, Chairs, Tables',
            'multi_floor' => true,
            'total_floor' => 2,
        ]);
    }
}
