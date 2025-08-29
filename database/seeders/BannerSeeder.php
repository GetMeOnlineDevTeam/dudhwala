<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Banner::insert([
            [
                'banner' => 'banner/banner.png',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
