<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VenueAddress;

class VenueAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VenueAddress::create([
            'venue_id'   => 1, // make sure this venue exists
            'city'       => 'Vadodara',
            'state'      => 'Gujarat',
            'addr'       => 'Dudhwala Community Halls Vadodara (Panigate Branch)',
            'pincode'    => '390019',
            'google_link'=> 'https://maps.app.goo.gl/nP9PAV2pLT4AM4px6',
        ]);

        VenueAddress::create([
            'venue_id'   => 2,
            'city'       => 'Vadodara',
            'state'      => 'Gujarat',
            'addr'       => 'Dudhwala Community Halls Vadodara (Nagarwada Branch)',
            'pincode'    => '390019',
            'google_link'=> 'https://maps.app.goo.gl/JuBWPfbbj6f16oL86',
        ]);
    }
}
