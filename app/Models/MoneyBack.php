<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyBack extends Model
{
    use HasFactory;

    protected $table = 'money_back';

    public const TYPE_REFUND = 'Pay Back';
    public const TAKE_MONEY = 'Take Money';

    protected $fillable = [
        'user_id',      // <-- added
        'booking_id',
        'type',
        'amount',
        'reference',
        'note',
        'processed_at',
        'status'
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // Always available, even after refund deletion of booking
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Present when booking still exists (e.g., "return" case)
    public function booking()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }

    public function venue_details()
    {
        return $this->booking ? $this->booking->venue_details() : null;
    }

    public function timeSlot()
    {
        return $this->booking ? $this->booking->timeSlot() : null;
    }

    public function isRefund(): bool
    {
        return $this->type === self::TYPE_REFUND;
    }

    public function isReturn(): bool
    {
        return $this->type === self::TAKE_MONEY;
    }

    public function scopeRefund($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    public function scopeReturn($query)
    {
        return $query->where('type', self::TAKE_MONEY);
    }
}
