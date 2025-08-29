<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    /**
     * Create Razorpay Order
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            // Amount should be in paise (integer ≥ 1)
            'amount' => 'required|integer|min:1',
        ]);

        try {
            // Load key & secret from config/services.php (backed by .env)
            $key    = config('services.razorpay.key');
            $secret = config('services.razorpay.secret');

            $razorpay = new Api($key, $secret);

            $order = $razorpay->order->create([
                'receipt'         => 'order_' . uniqid(),
                'amount'          => $request->amount,   // already in paise
                'currency'        => 'INR',
                'payment_capture' => 1,                  // auto-capture
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $order->id,
                'amount'   => $order->amount,
                'currency' => $order->currency,
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay Order Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error'   => 'Failed to create payment order',
            ], 500);
        }
    }

    /**
     * Optional: Record payment success (without signature verification)
     */
    public function paymentSuccess(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
            'order_id'   => 'required|string',
            'amount'     => 'required|integer|min:1',
        ]);

        // TODO: verify signature and record payment in your DB

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
        ]);
    }
}
