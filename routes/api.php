<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\RazorpayController; // Add RazorpayController



// routes/api.php
Route::post('/razorpay/order', [RazorpayController::class, 'createOrder']);
Route::post('/razorpay/verify', [RazorpayController::class, 'verifyPayment']);


Route::post('/token-login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = User::where('email', $request->email)->first();

    return response()->json([
        'token' => $user->createToken('api-token')->plainTextToken,
        'user' => $user
    ]);
});
