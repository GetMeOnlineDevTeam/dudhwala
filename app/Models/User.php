<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    // Disable timestamps since your table no longer has created_at/updated_at
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'contact_number',
        'is_verified',
        'is_member',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'is_member' => 'boolean',
    ];

    /**
     * A user can have many OTP codes.
     */
    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

        public function documents()
    {
        return $this->hasMany(UserDocuments::class);
    }

    public function bookings()
    {
        return $this->hasMany(Bookings::class);
    }
}
