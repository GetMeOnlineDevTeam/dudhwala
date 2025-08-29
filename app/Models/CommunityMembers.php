<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityMembers extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'designation',
        'image',
        'priority',
        'is_visible',
    ];

    // Optional: Scopes
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    // Optional: Image accessor if needed
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
