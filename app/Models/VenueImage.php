<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'image',
        'is_cover'
    ];

    public function venue()
    {
        return $this->belongsTo(VenueDetail::class, 'venue_id');
    }
}
