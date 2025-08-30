<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
class ConfigurationSeeder extends Seeder
{
    public function run()
    {
        DB::table('configurations')->insert([
            ['key' => 'dudhwala_discount', 'value' => 10.00],
        ]);
    }
}
