<?php

namespace App\Services;

use Razorpay\Api\Api;
use Exception;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $key    = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');

        if (! $key || ! $secret) {
            throw new Exception('Razorpay credentials are missing.');
        }

        // pass both key and secret here
        $this->api = new Api($key, $secret);
    }

    /**
     * Create a Razorpay order
     *
     * @param float  $amount
     * @param string $currency
     * @return \Razorpay\Api\Request
     */
    public function createOrder($amount, $currency = 'INR')
    {
        $orderData = [
            'receipt'         => uniqid(),
            'amount'          => $amount * 100,
            'currency'        => $currency,
            'payment_capture' => 1,
        ];

        try {
            return $this->api->order->create($orderData);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Verify the payment signature
     *
     * @param array $paymentData
     * @return bool
     */
    public function verifyPaymentSignature($paymentData)
    {
        try {
            $this->api->utility->verifyPaymentSignature($paymentData);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
