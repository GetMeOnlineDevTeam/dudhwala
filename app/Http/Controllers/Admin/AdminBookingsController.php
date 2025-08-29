<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookings;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;


class AdminBookingsController extends Controller
{
    public function bookings(Request $request)
    {
        $query = Bookings::with(['user', 'venue', 'timeSlot', 'payment']);

        // Search by user name or venue name
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas(
                    'user',
                    fn($q2) =>
                    $q2->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%")
                )
                    ->orWhereHas(
                        'venue',
                        fn($q3) =>
                        $q3->where('name', 'like', "%{$s}%")
                    );
            });
        }

        // Date-based filter
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('booking_date', Carbon::today());
                    break;

                case 'this_week':
                    $start = Carbon::today()->startOfWeek();
                    $end   = Carbon::today()->endOfWeek();
                    $query->whereBetween('booking_date', [$start, $end]);
                    break;

                case 'this_year':
                    $query->whereYear('booking_date', Carbon::now()->year);
                    break;
            }
        }

        $bookings = $query
            ->orderBy('booking_date', 'asc') // Order by booking date ascending
            ->orderBy('id')
            ->paginate(10, ['*'], 'bookings_page')
            ->appends($request->only('search', 'date_filter'));

        return view('admin.bookings.index', compact('bookings'));
    }

    public function destroy(Bookings $booking)
    {
        $booking->delete();

        return redirect()
            ->route('admin.bookings')
            ->with('success', 'Booking #' . $booking->id . ' has been deleted.');
    }

    public function export(Request $request)
    {
        $filename = 'bookings_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new BookingsExport($request->only('search', 'date_filter')),
            $filename
        );
    }
}
