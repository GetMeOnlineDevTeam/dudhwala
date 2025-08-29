<?php

namespace App\Exports;

use App\Models\Bookings;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BookingsExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(protected array $filters = []) {}

    public function array(): array
    {
        $q = Bookings::with(['user', 'venue', 'timeSlot', 'payment']);

        // Search: user first/last or venue name
        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->whereHas('user', function ($u) use ($s) {
                    $u->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%");
                })
                    ->orWhereHas('venue', function ($v) use ($s) {
                        $v->where('name', 'like', "%{$s}%");
                    });
            });
        }

        // Date filter on booking_date
        if (!empty($this->filters['date_filter'])) {
            switch ($this->filters['date_filter']) {
                case 'today':
                    $q->whereDate('booking_date', Carbon::today());
                    break;
                case 'this_week':
                    $q->whereBetween('booking_date', [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()]);
                    break;
                case 'this_year':
                    $q->whereYear('booking_date', Carbon::now()->year);
                    break;
            }
        }

        return $q->orderBy('booking_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($b) {
                $isAdminUser = ($b->user->role ?? null) === 'admin';

                // Name
                $name = $isAdminUser
                    ? 'Admin'
                    : trim(($b->user->first_name ?? '') . ' ' . ($b->user->last_name ?? ''));

                // Time-slot
                $slot = $b->timeSlot->name ?? '';
                if ($b->full_time) {
                    $slot .= ' (Full Day)';
                }

                // Booking Date
                $bookingDate = '';
                if (!empty($b->booking_date)) {
                    $bookingDate = $b->booking_date instanceof \Illuminate\Support\Carbon
                        ? $b->booking_date->format('d M, Y')
                        : Carbon::parse($b->booking_date)->format('d M, Y');
                }

                // Booked on (created_at)
                $bookedOn = '';
                if (!empty($b->created_at)) {
                    $bookedOn = $b->created_at instanceof \Illuminate\Support\Carbon
                        ? $b->created_at->format('d M, Y H:i')
                        : Carbon::parse($b->created_at)->format('d M, Y H:i');
                }

                if ($isAdminUser) {
                    // Mirror the table behavior: show “Bookings Unavailable” across the last columns.
                    return [
                        $b->id,
                        $name,
                        $b->venue->name ?? '',
                        $slot,
                        $bookingDate,
                        'Bookings Unavailable', // Payment
                        '',                     // Status
                        '',                     // Booked on
                    ];
                }

                // Normal row
                $amount = $b->payment->amount ?? null;
                $status = $b->status ?? '';

                return [
                    $b->id,
                    $name,
                    $b->venue->name ?? '',
                    $slot,
                    $bookingDate,
                    $amount !== null ? (float)$amount : '', // keep numeric when possible
                    $status,
                    $bookedOn,
                ];
            })
            ->toArray();
    }

    public function headings(): array
    {
        // Match your table (omit “Actions” in export)
        return ['ID', 'Name', 'Venue', 'Time-slot', 'Booking Date', 'Payment', 'Status', 'Booked on'];
    }

    public function styles(Worksheet $sheet)
    {
        // Autosize + bold header
        $cols = count($this->headings());
        for ($i = 1; $i <= $cols; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex($cols) . '1')->getFont()->setBold(true);
        return [];
    }
}
