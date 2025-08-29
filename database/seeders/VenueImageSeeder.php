<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\VenueDetail;
use App\Models\VenueImage;

class VenueImageSeeder extends Seeder
{
    public function run(): void
    {
        $imageCount = 7;

        $venues = VenueDetail::all();

        foreach ($venues as $venue) {
            $sanitizedVenueName = Str::slug($venue->name, '_');

            for ($i = 1; $i <= $imageCount; $i++) {
                VenueImage::create([
                    'venue_id' => $venue->id,
                    'image'    => "venue_images/{$sanitizedVenueName}_{$i}.jpg",
                    'is_cover' => $i === 1, // Only the first image is cover
                ]);
            }
        }
    }
}
