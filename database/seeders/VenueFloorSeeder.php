<?php

namespace Database\Seeders;

use App\Models\VenueDetail;
use App\Models\VenueFloor;
use Illuminate\Database\Seeder;

class VenueFloorSeeder extends Seeder
{
    public function run(): void
    {
        $venues = VenueDetail::all();

        foreach ($venues as $venue) {
            // If the venue has only 1 floor, treat it as full venue booking
            if ($venue->total_floor == 1) {
                VenueFloor::create([
                    'venue_id' => $venue->id,
                    'floor_no' => 0,
                    'floor_price' => 4000,
                    'full_time_price' => 6000, // or pull from logic/random
                ]);
            }

            // If it has more than 1 floor, assign per-floor pricing
            else {
                for ($i = 0; $i < $venue->total_floor; $i++) {
                    VenueFloor::create([
                        'venue_id' => $venue->id,
                        'floor_no' => $i,
                        'floor_price' => 5000,
                        'full_time_price' => 8000,
                    ]);
                }
            }
        }
    }
}
