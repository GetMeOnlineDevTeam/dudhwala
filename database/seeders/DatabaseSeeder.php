<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

// Import models
use App\Models\User;
use App\Models\VenueDetail;
use App\Models\VenueAddress;
use App\Models\VenueImage;
use App\Models\VenueFloor;
use App\Models\VenueTimeSlot;
use App\Models\Banner;
use App\Models\CommunityMoment;
use App\Models\CommunityMembers;  
use App\Models\MemberContact;
use App\Models\ContactRequest;
use App\Models\Policies;          
use App\Models\Configuration;
use App\Models\Item;
use App\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Truncate your tables (no Spatie tables)
        User::truncate();
        VenueDetail::truncate();
        VenueAddress::truncate();
        VenueImage::truncate();
        VenueFloor::truncate();
        VenueTimeSlot::truncate();
        Banner::truncate();
        CommunityMoment::truncate();
        CommunityMembers::truncate();
        MemberContact::truncate();
        ContactRequest::truncate();
        Policies::truncate();
        Configuration::truncate();
        Item::truncate();
        Permission::truncate();

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        // Call seeders (removed RolesPermissionsSeeder)
        $this->call([
            AdminSeeder::class,
             PermissionSeeder::class,
            VenueDetailSeeder::class,
            VenueAddressSeeder::class,
            VenueImageSeeder::class,
            VenueFloorSeeder::class,
            VenueTimeSlotSeeder::class,
            BannerSeeder::class,
            CommunityMomentSeeder::class,
            CommunityMemberSeeder::class,
            MemberContactSeeder::class,
            ContactRequestSeeder::class,
            PolicySeeder::class,
            ConfigurationSeeder::class,
            ItemSeeder::class,
            // AvailabilitySeeder::class,
        ]);
    }
}
