<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    // The table name is inferred ("contact_requests"), so no need to specify

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone_no',
        'subject',
        'message',
    ];
}
