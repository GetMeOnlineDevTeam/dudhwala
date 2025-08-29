<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailableTimeSlot extends Model
{
    // If you followed the migration exactly, this is optional
    protected $table = 'available_time_slots';

    // Mass-assignable fields
    protected $fillable = [
        'venue_id',
        'floor_no',
        'time_slot_id',
        'date',
        'is_available',
    ];

    // Casts
    protected $casts = [
        'floor_no'    => 'integer',
        'date'        => 'date',
        'is_available'=> 'boolean',
    ];

    /**
     * The venue this availability belongs to.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(VenueDetail::class, 'venue_id');
    }

    /**
     * The time‐slot this availability refers to.
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(VenueTimeSlot::class, 'time_slot_id');
    }
}
