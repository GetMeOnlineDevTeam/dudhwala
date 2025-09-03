<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::guard('admin')->attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        /** @var \App\Models\User $user */
        $user = Auth::guard('admin')->user();

       if (! in_array(strtolower($user->role ?? ''), ['admin', 'superadmin'], true)) {
    Auth::guard('admin')->logout();
    return back()->withErrors(['email' => 'You do not have admin access.']);
}

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        return redirect()->route('admin.login')
            ->with('status', 'Logged out successfully');
    }
}
