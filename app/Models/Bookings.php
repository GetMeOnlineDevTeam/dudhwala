<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Bookings extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'bookings';
protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'venue_id',
        'floor_id',
        'full_venue',
        'full_time',
        'time_slot_id',
        'booking_date',
        'status',
        'payment_id',
        'deposit_amount',
        'items_amount',
        'discount',
        'community'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'status' => 'string',
        'booking_date' => 'date', // or 'date:Y-m-d'
    ];

    public function items(): HasMany
    {
        // FK on booking_items = booking_id ; local key on bookings = id
        return $this->hasMany(BookingItem::class, 'booking_id', 'id');
    }
    /**
     * A Booking belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A Booking belongs to a VenueDetail.
     */
    public function venue()
    {
        return $this->belongsTo(VenueDetail::class, 'venue_id');
    }
    
    public function venue_details()
    {
        return $this->belongsTo(VenueDetail::class, 'venue_id');  // Ensure 'venue_id' is the correct foreign key
    }
    /**
     * A Booking belongs to a Floor.
     */
    public function floor()
    {
        return $this->belongsTo(VenueFloor::class, 'floor_id');
    }

    /**
     * A Booking belongs to a VenueTimeSlot.
     */
    public function timeSlot()
    {
        return $this->belongsTo(VenueTimeSlot::class, 'time_slot_id');
    }

    /**
     * A Booking may have a Payment.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }



    // in App\Models\Bookings.php
    public function bookings()
    {
        return $this->hasMany(Bookings::class);
    }
}
