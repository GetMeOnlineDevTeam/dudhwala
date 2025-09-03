<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder {
  public function run(): void {
    $data = [
      ['name' => 'Chair'],
      ['name' => 'Fan'],
      ['name' => 'Thali'],
    ];
    foreach ($data as $d) {
      Item::updateOrCreate(['name' => $d['name']], $d);
    }
  }
}