<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bookings;
use App\Models\Payment;
use App\Models\VenueTimeSlot;
use App\Models\MoneyBack;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:dashboard.view'))->only('dashboard'),
    ];
}


    public function dashboard()
{
    // Users
    $users = User::where('role', 'user')
        ->orderBy('id', 'desc')
        ->paginate(10, ['*'], 'users_page');

    // Bookings
    $bookings = Bookings::with(['user', 'venue', 'timeSlot'])
        ->whereHas('user', fn($q) => $q->where('role', 'user'))
        ->orderBy('id', 'desc')
        ->paginate(5, ['*'], 'bookings_page');


    // ---- Totals ----

    // Only count real paid/completed payments toward revenue
    $paymentsTotal = Payment::whereHas('user', fn($q) => $q->where('role', 'user'))
        ->whereIn('status', ['paid', 'completed', 'success']) // adjust if your app uses different labels
        ->sum('amount');

    // MoneyBack adjustments:
    // Include rows with NULL status (legacy) or processed-like statuses.
    // Exclude 'pending'.
    $processedStatus = ['processed', 'completed', 'success', 'paid'];

    $moneyBackAdd = MoneyBack::whereHas('user', fn($q) => $q->where('role', 'user'))
        ->where('type', 'Take Money')
        ->where(function ($q) use ($processedStatus) {
            $q->whereNull('status')->orWhereIn('status', $processedStatus);
        })
        ->sum('amount');

    $moneyBackSub = MoneyBack::whereHas('user', fn($q) => $q->where('role', 'user'))
        ->where('type', 'Pay Back')
        ->where(function ($q) use ($processedStatus) {
            $q->whereNull('status')->orWhereIn('status', $processedStatus);
        })
        ->sum('amount');

    // Net revenue = payments + (take money) - (pay back)
    $netRevenue = (float) $paymentsTotal + (float) $moneyBackAdd - (float) $moneyBackSub;

    // (Optional) list for MoneyBack table
    $moneyBacks = MoneyBack::with(['user', 'booking.venue', 'booking.timeSlot'])
        ->latest()
        ->paginate(5, ['*'], 'money_back_page');

    // If you still want a standalone "money back total" for the UI, you can present the breakdown:
    $moneyBackTotal = (float) $moneyBackAdd - (float) $moneyBackSub;

    return view('admin.index', compact(
        'users',
        'bookings',
        'paymentsTotal',
        'moneyBacks',
        'moneyBackTotal',
        'netRevenue'
    ));
}

}
