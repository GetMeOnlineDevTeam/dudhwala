<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommunityMoment;

class CommunityMomentSeeder extends Seeder
{
    public function run(): void
    {
        CommunityMoment::insert([
            [
                'description' => 'Community gathering at the mosque.',
                'image' => 'community_moments/1.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Volunteers distributing food packets.',
                'image' => 'community_moments/2.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Children performing at cultural event.',
                'image' => 'community_moments/3.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
