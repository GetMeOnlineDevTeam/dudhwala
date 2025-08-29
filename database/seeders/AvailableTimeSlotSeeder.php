<?php

use Illuminate\Database\Seeder;
use App\Models\VenueDetail;
use App\Models\VenueTimeSlot;
use App\Models\AvailableTimeSlot;
use Carbon\Carbon;

class AvailableTimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $start = Carbon::today();
        $end   = Carbon::today()->addDays(30);

        VenueDetail::with('floors')->get()->each(function($venue) use ($start, $end) {
            $slots = VenueTimeSlot::where('venue_id', $venue->id)->get();
            $dates = collect();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dates->push($d->toDateString());
            }

            foreach ($dates as $date) {
                // full-venue rows
                foreach ($slots as $slot) {
                    AvailableTimeSlot::create([
                        'venue_id'     => $venue->id,
                        'floor_no'     => null,
                        'time_slot_id' => $slot->id,
                        'date'         => $date,
                    ]);
                }

                // per-floor rows
                foreach ($venue->floors as $floor) {
                    foreach ($slots as $slot) {
                        AvailableTimeSlot::create([
                            'venue_id'     => $venue->id,
                            'floor_no'     => $floor->floor_no,
                            'time_slot_id' => $slot->id,
                            'date'         => $date,
                        ]);
                    }
                }
            }
        });
    }
}
