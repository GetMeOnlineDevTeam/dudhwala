<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueTimeSlot extends Model
{
    use HasFactory;

    protected $table = 'venue_time_slots'; // Explicit table name
    // Mass‐assignable fields
    protected $fillable = [
        'venue_id',
        'floor_id', 
        'name',
        'start_time',
        'end_time',
        'single_time',
        'full_venue',
        'full_time',
        'price',
        'deposit'
    ];

    // Cast columns to proper types
    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time'   => 'datetime:H:i:s',
        'single_time' => 'boolean',
        'full_venue' => 'boolean',
        'full_time'  => 'boolean',
        'price'      => 'integer',
        'deposit'     => 'integer',
    ];
    /**
     * Relationship to VenueDetail
     */
    public function venue()
    {
        return $this->belongsTo(VenueDetail::class, 'venue_id');
    }

    public function floor()
    {
        return $this->belongsTo(VenueFloor::class, 'floor_id');
    }
}
