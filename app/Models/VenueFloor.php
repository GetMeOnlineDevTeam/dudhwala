<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueFloor extends Model
{
    use HasFactory;
    protected $table = 'venue_floors'; // Explicit table name
    protected $fillable = [
        'venue_id',
        'floor_no',
        'floor_price',
        'full_time_price',
    ];

    public function venue()
    {
        return $this->belongsTo(VenueDetail::class, 'venue_id');
    }

    public function timeSlots()
    {
        return $this->hasMany(VenueTimeSlot::class, 'floor_id');
    }
}
