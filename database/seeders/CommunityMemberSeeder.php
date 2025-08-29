<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunityMemberSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('community_members')->insert([
            [
                'name' => 'Mr. Ali Akbar Shaikh',
                'designation' => 'Hon. President',
                'image' => 'community_members/1.png',
                'priority' => 1,
            ],
            [
                'name' => 'Mr. Mohamad Raza',
                'designation' => 'Hon. Vice President',
                'image' => 'community_members/2.png',
                'priority' => 2,
            ],
            [
                'name' => 'Mr. Sajjad Shaikh',
                'designation' => 'Hon. Secretary',
                'image' => 'community_members/3.png',
                'priority' => 3,
            ],
            [
                'name' => 'Mr. Irfan Mohsin Karim',
                'designation' => 'Hon. Joint Secretary',
                'image' => 'community_members/4.png',
                'priority' => 4,
            ],
            [
                'name' => 'Mr. Rizwan Battiwala',
                'designation' => 'Hon. Treasurer',
                'image' => 'community_members/5.png',
                'priority' => 5,
            ],
            [
                'name' => 'Mr. Nadeem Halani',
                'designation' => 'Hon. Joint Treasurer',
                'image' => 'community_members/6.png',
                'priority' => 6,
            ],
                        [
                'name' => 'Mr. Nadeem Halani',
                'designation' => 'Hon. Joint Treasurer',
                'image' => 'community_members/6.png',
                'priority' => 7,
            ],
            [
                'name' => 'Mr. Nadeem Halani',
                'designation' => 'Hon. Joint Treasurer',
                'image' => 'community_members/6.png',
                'priority' => 8,
            ],
        ]);
    }
}
