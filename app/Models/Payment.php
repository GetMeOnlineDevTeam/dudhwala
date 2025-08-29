<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $table = 'payments';
    protected $fillable = [
        'user_id',
        'booking_id',
        'amount',
        'method',
        'razorpay_order_id',
        'razorpay_payment_id',
        'offline_reference',
        'status',
        'paid_at',
    ];

    public function bookings()
    {
        return $this->hasMany(Bookings::class, 'payment_id');
    }

    // relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Bookings::class);
    }
}
