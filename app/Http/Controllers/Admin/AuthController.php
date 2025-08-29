<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login'); // path: resources/views/admin/auth/login.blade.php
    }

    public function login(Request $request)
    {
        // Hard-coded admin credentials
        $admin = User::where('first_name', 'admin')
            ->where('contact_number', 'admin@123')
            ->first();

        if ($admin) {
            Auth::guard('admin')->login($admin);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'first_name' => 'The provided credentials are incorrect.'
        ]);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('login')->with('status', 'Logged out successfully');
    }
}
