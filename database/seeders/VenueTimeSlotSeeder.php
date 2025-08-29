<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VenueDetail;
use App\Models\VenueTimeSlot;
use App\Models\VenueFloor;

class VenueTimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Base slot patterns
        $baseSlots = [
            [
                'label'      => 'Morning',
                'start_time' => '07:00:00',
                'end_time'   => '16:00:00',
                'full_time'  => false,
                'base_price' => 5000,
            ],
            [
                'label'      => 'Evening',
                'start_time' => '16:30:00',
                'end_time'   => '23:00:00',
                'full_time'  => false,
                'base_price' => 5000,
            ],
            [
                'label'      => 'Full Day',
                'start_time' => '07:00:00',
                'end_time'   => '23:00:00',
                'full_time'  => true,
                'base_price' => 12000,
            ],
        ];

        VenueDetail::all()->each(function (VenueDetail $venue) use ($baseSlots) {
            // Optional: clear previous slots for idempotent seeding
            // VenueTimeSlot::where('venue_id', $venue->id)->delete();

            // Pull floors for this venue
            $floors = VenueFloor::where('venue_id', $venue->id)
                ->orderBy('floor_no')
                ->get();

            // If a single-floor venue has no floor row yet, create one
            if (! $venue->multi_floor && $floors->isEmpty()) {
                $floors = collect([
                    VenueFloor::create([
                        'venue_id'      => $venue->id,
                        'floor_no'      => 1,
                        'floor_price'   => 0,     // adjust if you use this
                        'full_time_price' => 0,   // adjust if you use this
                    ])
                ]);
            }

            if (! $venue->multi_floor) {
                // SINGLE-FLOOR → seed slots with a REAL floor_id (no NULL)
                $floor = $floors->first(); // guaranteed by block above
                foreach ($baseSlots as $slot) {
                    VenueTimeSlot::create([
                        'venue_id'   => $venue->id,
                        'floor_id'   => $floor->id, // <- never NULL
                        'name'       => "{$slot['label']} ({$slot['start_time']} – {$slot['end_time']})",
                        'start_time' => $slot['start_time'],
                        'end_time'   => $slot['end_time'],
                        'full_venue' => true,                 // keep your original flagging
                        'full_time'  => $slot['full_time'],
                        'price'      => $slot['base_price'],
                    ]);
                }
                return;
            }

            // MULTI-FLOOR
            // 1) Per-floor copies (each with a real floor_id)
            foreach ($floors as $floor) {
                foreach ($baseSlots as $slot) {
                    VenueTimeSlot::create([
                        'venue_id'   => $venue->id,
                        'floor_id'   => $floor->id, // FK to venue_floors.id
                        'name'       => "Floor {$floor->floor_no}: {$slot['label']} ({$slot['start_time']} – {$slot['end_time']})",
                        'start_time' => $slot['start_time'],
                        'end_time'   => $slot['end_time'],
                        'full_venue' => false,
                        'full_time'  => $slot['full_time'],
                        'price'      => $slot['base_price'], // per-floor base
                    ]);
                }
            }

            // 2) (Optional) All-floors / full-venue variants.
            // If you DON'T want these, remove this block.
            // $floorCount = max(1, $floors->count());
            // foreach ($baseSlots as $slot) {
            //     VenueTimeSlot::create([
            //         'venue_id'   => $venue->id,
            //         'floor_id'   => null, // intentionally NULL only for the "whole venue" choice
            //         'name'       => "All Floors: {$slot['label']} ({$slot['start_time']} – {$slot['end_time']})",
            //         'start_time' => $slot['start_time'],
            //         'end_time'   => $slot['end_time'],
            //         'full_venue' => true,
            //         'full_time'  => $slot['full_time'],
            //         'price'      => $slot['base_price'] * $floorCount,
            //     ]);
            // }
        });
    }
}
