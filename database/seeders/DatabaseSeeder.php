<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // Add your admin user here
        // User::updateOrCreate(
        //     ['email' => 'admin@gmail.com'],
        //     [
        //         'name' => 'Admin',
        //         'password' => Hash::make('admin@123'),
        //         'role' => 'admin', // make sure your users table has a `role` column
        //     ]
        // );

        // $this->call([
        //     RolesTableSeeder::class, // Ensure you have a UserSeeder to seed other users
        // ]);

        $this->call([
            AdminSeeder::class,
        ]);

        $this->call([
            VenueDetailSeeder::class,
        ]);

        $this->call([
            VenueAddressSeeder::class,
        ]);

        $this->call([
            VenueImageSeeder::class,
        ]);

        $this->call([
            VenueFloorSeeder::class,
        ]);

        $this->call([
            VenueTimeSlotSeeder::class,
        ]);

        $this->call([
            BannerSeeder::class,
        ]);

        $this->call([
            CommunityMomentSeeder::class,
        ]);

        $this->call([
            CommunityMemberSeeder::class,
        ]);

        $this->call([
            MemberContactSeeder::class,
        ]);

        $this->call([
            ContactRequestSeeder::class,
        ]);

        $this->call([
            PolicySeeder::class,
        ]);

        // $this->call([
        //     AvailabilitySeeder::class,
        // ]);
    }
}
