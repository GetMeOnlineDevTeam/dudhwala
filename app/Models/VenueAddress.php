<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueAddress extends Model
{
    use HasFactory;

    protected $table = 'venue_addr';

    protected $fillable = [
        'venue_id',
        'city',
        'state',
        'addr',
        'pincode',
        'google_link',
    ];

    // Define relationship to VenueDetails (if applicable)
    public function venue()
    {
        return $this->belongsTo(VenueDetail::class, 'venue_id');
    }
}
