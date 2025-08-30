<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\CommunityMoment;
use App\Models\VenueDetail;
use App\Models\VenueImage;
use App\Models\VenueAddress;
use App\Models\CommunityMembers;
use App\Models\Configuration;
use App\Models\MemberContact;
use App\Models\Policies;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\VenueTimeSlot;

class HomeController extends Controller
{
    public function index()
    {
        $banner           = Banner::where('is_published', true)->first();
        $communityMoments = CommunityMoment::all();
        $venues           = VenueDetail::with([
            'images'  => fn($q) => $q->where('is_cover', true),
            'address'
        ])->get();

        // split members
        $allMembers   = CommunityMembers::orderBy('priority', 'asc')->get();
        $topMembers   = $allMembers->take(6);
        $moreMembers  = $allMembers->slice(6);

        // split generic venue images
        $coverImage  = VenueImage::where('is_cover', true)->first();
        $otherImages = VenueImage::where('is_cover', false)->take(6)->get();

        // **NEW**: grab the first active phone contact
        $activePhone = MemberContact::where('contact_type', 'phone')
            ->where('is_active', true)
            ->take(2)
            ->get();

        return view('index', compact(
            'banner',
            'communityMoments',
            'venues',
            'topMembers',
            'moreMembers',
            'coverImage',
            'otherImages',
            'activePhone'    // ← pass it to the view
        ));
    }

    public function venues()
    {

        $venues = VenueDetail::with(['images', 'address'])->get();

        $venues = $venues->map(function ($venue) {

            $venue->cover_image  = $venue->images->where('is_cover', true)->first();
            $venue->other_images = $venue->images->where('is_cover', false)->take(6);

            $rawIframe = $venue->address->google_link ?? '';
            $venue->mapLink = Str::between($rawIframe, 'src="', '"');

            return $venue;
        });

        $activePhoneContacts = MemberContact::where('contact_type', 'phone')
            ->where('is_active', true)
            ->get();

        return view('venues', compact('venues', 'activePhoneContacts'));
    }

    public function about()
    {

        $communityMembers = CommunityMembers::orderBy('priority', 'asc')->get();

        return view('about', compact('communityMembers'));
    }

    public function contact()
    {
        // // 1) Grab the raw iframe HTML
        // $rawIframe = VenueAddress::whereNotNull('google_link')
        //     ->value('google_link');

        // // 2) Extract the src URL
        // $mapLink = Str::between($rawIframe, 'src="', '"');

        // 3) Phones
        $activePhoneContacts = MemberContact::where('contact_type', 'phone')
            ->where('is_active', true)->get();

        return view('contact', compact('activePhoneContacts'));
    }

    public function bookHall()
    {
        $user = Auth::user();
        $venues = VenueDetail::all();
        $slots = VenueTimeSlot::all(); // This is from your model
         $dudhwalaDiscount = Configuration::where('key', 'dudhwala_discount')->first()->value ?? 0;
        // dd($slots);
        return view('book_hall', compact('user', 'venues', 'slots', 'dudhwalaDiscount'));
    }

    public function policies()
    {
        $policies = Policies::orderByRaw("FIELD(type, 'terms', 'privacy', 'refund')")->get();
        return view('policies', compact('policies'));
    }
}
