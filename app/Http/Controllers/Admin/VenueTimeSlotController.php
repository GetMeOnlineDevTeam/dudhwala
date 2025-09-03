<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VenueTimeSlot;
use Illuminate\Http\Request;



use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VenueTimeSlotController extends Controller implements HasMiddleware
{
    // VenueTimeSlotController
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        new Middleware('can:venues.manage'),
    ];
}


    // Add your methods for managing timeslots here
    // For example, you might have methods to create, update, delete, and list timeslots
}
