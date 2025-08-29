<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bookings;
use App\Models\Payment;
use App\Models\VenueTimeSlot;
use App\Models\MoneyBack;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Users (unchanged)
        $users = User::where('role', 'user')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'users_page');

        // Bookings (unchanged)
        $bookings = Bookings::with(['user', 'venue', 'timeSlot'])
            ->whereHas('user', fn($q) => $q->where('role', 'user'))
            ->orderBy('id', 'desc')
            ->paginate(5, ['*'], 'bookings_page');

        // Payments list (for table)
        $payments = Payment::with('user')
            ->whereHas('user', fn($q) => $q->where('role', 'user'))
            ->orderBy('id', 'desc')
            ->paginate(5, ['*'], 'payments_page');

        // Totals (use sums, not paginated collections)
        $paymentsTotal = Payment::whereHas('user', fn($q) => $q->where('role', 'user'))
            // ->where('status', 'completed') // uncomment if you only count completed
            ->sum('amount');

        $moneyBacks = MoneyBack::with(['user', 'booking.venue', 'booking.timeSlot'])
            ->latest()
            ->paginate(5, ['*'], 'money_back_page');

        $moneyBackTotal = MoneyBack::whereHas('user', fn($q) => $q->where('role', 'user'))
            ->sum('amount');

        $netRevenue = $paymentsTotal - $moneyBackTotal;

        return view('admin.index', compact(
            'users',
            'bookings',
            'payments',
            'paymentsTotal',
            'moneyBacks',
            'moneyBackTotal',
            'netRevenue'
        ));
    }
}
