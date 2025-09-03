<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookings;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminBookingsController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:bookings.view'))->only('bookings'),
        (new Middleware('can:bookings.export'))->only('export'),
        // If you keep destroy, add permission slug 'bookings.delete' in seeder and then:
        // (new Middleware('can:bookings.delete'))->only('destroy'),
    ];
}



    public function bookings(Request $request)
    {
        $query = Bookings::query()
            ->with([
                'user:id,first_name,last_name,role',
                'venue:id,name',
                'timeSlot:id,name',
                // include status & method so badges/method label render without nulls
                'payment:id,amount,status,method'
            ])
            // items subtotal as items_total
            ->withSum('items as items_total', 'total');

        // Search by user name or venue name
        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(function ($q) use ($s) {
                $q->whereHas('user', function ($q2) use ($s) {
                    $q2->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%");
                })->orWhereHas('venue', function ($q3) use ($s) {
                    $q3->where('name', 'like', "%{$s}%");
                });
            });
        }

        // Optional filters
        if ($request->filled('venue_id')) {
            $query->where('venue_id', $request->integer('venue_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // Date-based filter
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('booking_date', \Carbon\Carbon::today());
                    break;
                case 'this_week':
                    $start = \Carbon\Carbon::today()->startOfWeek();
                    $end   = \Carbon\Carbon::today()->endOfWeek();
                    $query->whereBetween('booking_date', [$start, $end]);
                    break;
                case 'this_year':
                    $query->whereYear('booking_date', \Carbon\Carbon::now()->year);
                    break;
            }
        }

        $bookings = $query
            ->orderBy('booking_date', 'asc')
            ->orderBy('id')
            ->paginate(10, ['*'], 'bookings_page')
            ->appends($request->only('search', 'date_filter', 'venue_id', 'status'));

        return view('admin.bookings.index', compact('bookings'));
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
