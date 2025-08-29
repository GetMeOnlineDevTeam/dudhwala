<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberContact extends Model
{
    // Explicitly specify the table name since it's non‑standard plural
    protected $table = 'members_contact';

    // Which attributes can be mass assigned
    protected $fillable = [
        'name',
        'contact_type',
        'contact',
        'is_active',
    ];

    // Cast is_active to boolean automatically
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
