<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueDetail extends Model
{
    use HasFactory;

    protected $table = 'venue_details';
    protected $fillable = [
        'name',
        'about',
        'amenities',
        'multi_floor',
        'total_floor',
    ];

    public function images()
    {
        return $this->hasMany(VenueImage::class, 'venue_id');
    }

    public function address()
    {
        return $this->hasOne(VenueAddress::class, 'venue_id');
    }

    public function floors()
    {
        return $this->hasMany(VenueFloor::class, 'venue_id');
    }

    public function timeSlots()
    {
        return $this->hasMany(VenueTimeSlot::class, 'venue_id');
    }
}
