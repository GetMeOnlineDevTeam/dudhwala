<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bookings;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;


use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminUserController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:users.view'))->only('users','ShowProfile','export','verify'),
        // If you want a separate verify permission, add 'users.verify' and switch .only('verify') to it.
    ];
}

    

    public function users(Request $request)
    {
        // Base query
        $base = User::where('role', 'user');

        // Counts
        $total      = (clone $base)->count();
        $verified   = (clone $base)->where('is_verified', 1)->count();
        $notVerified = (clone $base)->where('is_verified', 0)->count();

        // Apply filters & paginate
        $users = $base
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where(
                    fn($q2) =>
                    $q2->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name',  'like', "%{$s}%")
                )
            )
            ->when(
                $request->filled('verification'),
                fn($q, $v) =>
                $q->where('is_verified', (int)$v)
            )
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends($request->only('search', 'verification'));

        return view('admin.users.index', compact(
            'users',
            'total',
            'verified',
            'notVerified'
        ));
    }
    public function ShowProfile(User $user)
    {
        $user->load('documents');
        $bookings = $user->bookings()
            ->with(['venue', 'timeSlot', 'payment'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.users.edit', compact('user', 'bookings'));
    }

    /**
     * Mark a user as verified.
     */
    public function verify(Request $request, User $user)
    {
        // Only allow 'accept' or 'reject'
        $action = $request->input('action');
        if (! in_array($action, ['accept', 'reject'])) {
            abort(400, 'Invalid action');
        }

        // Update the flag
        $user->is_verified = ($action === 'accept');
        $user->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'User has been ' . ($user->is_verified ? 'accepted' : 'rejected') . '.');
    }

    public function export(Request $request)
    {
        $filename = 'users_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new UsersExport($request->only('search', 'verification')),
            $filename
        );
    }
}
