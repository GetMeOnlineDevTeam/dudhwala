<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use Illuminate\Http\Request as HttpRequest;
use Carbon\Carbon;

class AdminContactRequestController extends Controller
{
    public function index(HttpRequest $request)
    {
        $query = ContactRequest::query();

        // Free-text search
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name',  'like', "%{$search}%")
                    ->orWhere('phone_no',   'like', "%{$search}%")
                    ->orWhere('subject',    'like', "%{$search}%")
                    ->orWhere('message',    'like', "%{$search}%");
            });
        }

        $from = $request->input('from');
        $to   = $request->input('to');
        if ($from) {
            try {
                $fromDate = Carbon::parse($from)->startOfDay();
                $query->where('created_at', '>=', $fromDate);
            } catch (\Throwable $e) {
            }
        }
        if ($to) {
            try {
                $toDate = Carbon::parse($to)->endOfDay();
                $query->where('created_at', '<=', $toDate);
            } catch (\Throwable $e) {
            }
        }

        $allowedSorts = ['created_at', 'first_name', 'last_name', 'phone_no', 'subject'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'created_at';
        $dir  = strtolower($request->input('dir')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $perPage  = (int) $request->input('per_page', 20);
        $perPage  = $perPage > 0 ? min($perPage, 100) : 20;
        $requests = $query->paginate($perPage)->withQueryString();

        return view('admin.Contact_Requests.index', [
            'requests' => $requests,
            'filters'  => [
                'q'        => $search ?? '',
                'from'     => $from ?? '',
                'to'       => $to ?? '',
                'sort'     => $sort,
                'dir'      => $dir,
                'per_page' => $perPage,
            ],
        ]);
    }




    /**
     * DELETE /admin/contact-requests/{request}
     */
    public function destroy(ContactRequest $request)
    {
        $request->delete();

        return redirect()
            ->route('admin.contact-requests')
            ->with('success', 'Contact request deleted successfully.');
    }
}
