<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VenueDetail;
use App\Models\VenueAddress;
use App\Models\VenueImage;
use App\Models\VenueTimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VenueDetailsController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:venues.view'))->only('venues'),
        (new Middleware('can:venues.create'))->only('create','store'),
        (new Middleware('can:venues.edit'))->only('edit','update'),
        (new Middleware('can:venues.delete'))->only('destroy'),
    ];
}



    /**
     * List venues with optional search.
     */
    public function venues(Request $request)
    {
        $query = VenueDetail::with(['address', 'images']);

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where('name', 'like', "%{$s}%");
        }

        $venues = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->only('search'));

        return view('admin.venue.index', compact('venues'));
    }

    public function create()
    {
        return view('admin.venue.create');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'about'             => 'nullable|string',
            'amenities'         => 'nullable|string',
            'multi_floor'       => 'sometimes|boolean',
            'total_floor'       => 'nullable|integer|min:0',

            'address.city'      => 'required|string|max:255',
            'address.state'     => 'nullable|string|max:255',
            'address.pincode'   => 'nullable|string|max:20',
            'address.addr'      => 'nullable|string',
            'address.google_link' => 'nullable|url',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'gallery_images'    => 'nullable|array',
            'gallery_images.*'  => 'image|mimes:jpeg,png,jpg,gif,svg|max:4096',

            'timeslots'               => 'nullable|array',
            'timeslots.*.name'        => 'required|string|max:255',
            'timeslots.*.start_time'  => 'required|date_format:H:i',
            'timeslots.*.end_time'    => 'required|date_format:H:i|after:start_time',
            'timeslots.*.price'       => 'required|numeric|min:0',
            'timeslots.*.single_time' => 'sometimes|boolean',
            'timeslots.*.full_time'   => 'sometimes|boolean',
            'timeslots.*.full_venue'  => 'sometimes|boolean',
            'timeslots.*.deposit_amount' => 'nullable|numeric|min:0',

        ]);

        $isMulti = (bool)($data['multi_floor'] ?? false);
        $totalFloors = $isMulti ? max(1, (int)($data['total_floor'] ?? 1)) : 0;

        DB::transaction(function () use ($request, $data, $isMulti, $totalFloors) {
            $venue = VenueDetail::create([
                'name'        => $data['name'],
                'about'       => $data['about'] ?? null,
                'amenities'   => $data['amenities'] ?? null,
                'multi_floor' => $isMulti,
                'total_floor' => $totalFloors,
            ]);

            $venue->address()->create([
                'city'        => $data['address']['city'],
                'state'       => $data['address']['state'] ?? null,
                'addr'        => $data['address']['addr'] ?? null,
                'pincode'     => $data['address']['pincode'] ?? null,
                'google_link' => $data['address']['google_link'] ?? null,
            ]);

            // Base folder (no backslashes!)
            $base = "venue_images";

            // Cover (save relative path like 'venue_images/3/vcover_xxx.jpg')
            if ($request->hasFile('cover_image')) {
                $name = 'vcover_' . uniqid() . '.' . $request->file('cover_image')->getClientOriginalExtension();
                $request->file('cover_image')->storeAs($base, $name, 'public'); // same as banner solution
                $venue->images()->create([
                    'image'    => "{$base}/{$name}",
                    'is_cover' => true,
                ]);
            }

            // Gallery
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $img) {
                    $name = 'vimg_' . uniqid() . '.' . $img->getClientOriginalExtension();
                    $img->storeAs($base, $name, 'public');
                    $venue->images()->create([
                        'image'    => "{$base}/{$name}",
                        'is_cover' => false,
                    ]);
                }
            }

            // Timeslots
            foreach (($data['timeslots'] ?? []) as $ts) {
                $venue->timeSlots()->create([
                    'name'        => $ts['name'],
                    'start_time'  => $ts['start_time'],
                    'end_time'    => $ts['end_time'],
                    'price'       => $ts['price'],
                    'deposit'      => isset($ts['deposit_amount']) ? (float)$ts['deposit_amount'] : 0, // map safely                    'single_time' => (bool)($ts['single_time'] ?? false),
                    'full_time'   => (bool)($ts['full_time'] ?? false),
                    'full_venue'  => (bool)($ts['full_venue'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('admin.venues')
            ->with('success', 'Venue created successfully.');
    }


    public function edit(VenueDetail $venue)
    {
        $venue->load(['address', 'images', 'floors', 'timeSlots']);
        return view('admin.venue.edit', compact('venue'));
    }

    public function update(Request $request, VenueDetail $venue)
    {
        // dd($request->all());
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'about'                => 'nullable|string',
            'amenities'            => 'nullable|string',
            'multi_floor'          => 'sometimes|boolean',
            'total_floor'          => 'nullable|integer|min:1',
            'address.city'         => 'required|string|max:100',
            'address.state'        => 'nullable|string|max:100',
            'address.addr'         => 'nullable|string|max:500',
            'address.pincode'      => 'nullable|string|max:20',
            'address.google_link'  => 'nullable|url|max:255',

            'cover_image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
            'gallery_images'       => 'nullable|array',
            'gallery_images.*'     => 'image|mimes:jpeg,png,jpg,gif,svg|max:5048',

            'delete_image_ids'     => 'nullable|array',
            'delete_image_ids.*'   => 'integer|exists:venue_images,id',

            'timeslots'               => 'nullable|array',
            'timeslots.*.id'          => 'required|integer|exists:venue_time_slots,id',
            'timeslots.*.name'        => 'required|string|max:255',
            'timeslots.*.start_time'  => 'required|date_format:H:i',
            'timeslots.*.end_time'    => 'required|date_format:H:i|after:start_time',
            'timeslots.*.price'       => 'required|numeric|min:0',
            'timeslots.*.single_time' => 'sometimes|boolean',
            'timeslots.*.full_time'   => 'sometimes|boolean',
            'timeslots.*.full_venue'  => 'sometimes|boolean',
            'timeslots.*.deposit_amount' => 'nullable|numeric|min:0',

        ]);

        $isMulti = (bool)($data['multi_floor'] ?? false);
        $totalFloors = (int)($data['total_floor'] ?? 0);
        if (!$isMulti) {
            $totalFloors = 0;
        } else {
            $totalFloors = max(1, $totalFloors);
        }

        DB::transaction(function () use ($request, $venue, $data, $isMulti, $totalFloors) {

            $venue->update([
                'name'        => $data['name'],
                'about'       => $data['about'] ?? null,
                'amenities'   => $data['amenities'] ?? null,
                'multi_floor' => $isMulti,
                'total_floor' => $totalFloors,
            ]);

            $venue->address()->updateOrCreate(
                ['venue_id' => $venue->id],
                [
                    'city'        => $data['address']['city'],
                    'state'       => $data['address']['state'] ?? null,
                    'addr'        => $data['address']['addr'] ?? null,
                    'pincode'     => $data['address']['pincode'] ?? null,
                    'google_link' => $data['address']['google_link'] ?? null,
                ]
            );

            $deleteIds = collect($data['delete_image_ids'] ?? [])->map(fn($v) => (int)$v)->all();
            if (!empty($deleteIds)) {
                $toDelete = $venue->images()->whereIn('id', $deleteIds)->get();
                foreach ($toDelete as $img) {
                    if (!empty($img->image)) {
                        Storage::disk('public')->delete($img->image);
                    }
                    $img->delete();
                }
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $uploaded) {
                    $ext  = $uploaded->getClientOriginalExtension();
                    $name = uniqid('vimg_') . '.' . $ext;
                    $uploaded->storeAs("public/venue_images/{$venue->id}", $name);

                    $venue->images()->create([
                        'image'    => "venue_images/{$name}", // stored without 'public/' prefix
                        'is_cover' => false,
                    ]);
                }
            }

            if ($request->hasFile('cover_image')) {
                $newCover = $request->file('cover_image');
                $oldCover = $venue->images()->where('is_cover', true)->first();

                if ($oldCover && $oldCover->image) {
                    Storage::disk('public')->delete($oldCover->image);
                }

                $ext  = $newCover->getClientOriginalExtension();
                $name = uniqid('vcover_') . '.' . $ext;
                $newCover->storeAs("public/venue_images/{$venue->id}", $name);

                // ensure only one cover
                $venue->images()->update(['is_cover' => false]);

                if ($oldCover) {
                    $oldCover->image   = "venue_images/{$name}";
                    $oldCover->is_cover = true;
                    $oldCover->save();
                } else {
                    $venue->images()->create([
                        'image'    => "venue_images/{$name}",
                        'is_cover' => true,
                    ]);
                }
            }
            if (!empty($data['timeslots'])) {
                foreach ($data['timeslots'] as $ts) {
                    $payload = [
                        'venue_id'    => $venue->id,
                        'name'        => $ts['name'],
                        'start_time'  => $ts['start_time'], // 'H:i'
                        'end_time'    => $ts['end_time'],   // 'H:i'
                        'price'       => $ts['price'],
                        'deposit'      => isset($ts['deposit_amount']) ? (float)$ts['deposit_amount'] : 0, // map safely                        'single_time' => (bool)($ts['single_time'] ?? false),
                        'full_time'   => (bool)($ts['full_time'] ?? false),
                        'full_venue'  => (bool)($ts['full_venue'] ?? false),
                    ];

                    VenueTimeSlot::where('id', (int)$ts['id'])
                        ->where('venue_id', $venue->id)
                        ->update($payload);
                }
            }
        });

        return redirect()
            ->route('admin.venues')
            ->with('success', 'Venue updated successfully.');
    }


    public function destroy($id)
    {
        $venue = VenueDetail::with(['address', 'floors', 'images', 'timeSlots'])->findOrFail($id);

        DB::transaction(function () use ($venue) {
            // Delete images from storage
            foreach ($venue->images as $image) {
                if ($image->image) {
                    Storage::delete('public/' . $image->image);
                }
            }

            // Delete related records
            $venue->images()->delete();
            $venue->address()->delete();
            $venue->floors()->delete();
            $venue->timeSlots()->delete();

            // Delete the venue itself
            $venue->delete();
        });

        return redirect()
            ->route('admin.venues')
            ->with('success', 'Venue deleted successfully.');
    }
}
