<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademyPayment;
use App\Models\ClickTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClickController extends Controller
{
    private $service_id;
    private $merchant_id;
    private $secret_key;

    public function __construct()
    {
        $this->service_id = env('CLICK_SERVICE_ID');
        $this->merchant_id = env('CLICK_MERCHANT_ID');
        $this->secret_key = env('CLICK_SECRET_KEY');
    }

    public function handle(Request $request)
    {
        $action = (int)$request->input('action');
        
        Log::channel('payment')->info('Click Callback', $request->all());

        switch ($action) {
            case 0:
                return $this->prepare($request);
            case 1:
                return $this->complete($request);
            default:
                return response()->json(['error' => -3, 'error_note' => 'Action not found'], 400);
        }
    }

    private function prepare(Request $request)
    {
        $click_trans_id = $request->input('click_trans_id');
        $merchant_trans_id = $request->input('merchant_trans_id');
        $amount = (float)$request->input('amount');
        $sign_time = $request->input('sign_time');
        $sign_string = $request->input('sign_string');
        $action = 0;

        // Verify sign
        $my_sign = md5($click_trans_id . $this->service_id . $this->secret_key . $merchant_trans_id . $amount . $action . $sign_time);
        if ($my_sign !== $sign_string) {
            return response()->json(['error' => -1, 'error_note' => 'SIGN CHECK FAILED']);
        }

        // Check local transaction
        $payment = AcademyPayment::find($merchant_trans_id);
        if (!$payment) {
            return response()->json(['error' => -5, 'error_note' => 'TRANSACTION NOT FOUND']);
        }
        if (abs($payment->amount - $amount) > 0.01) {
            return response()->json(['error' => -2, 'error_note' => 'INCORRECT AMOUNT']);
        }

        // Record or Update Log
        ClickTransaction::updateOrCreate(
            ['click_trans_id' => $click_trans_id],
            [
                'merchant_trans_id' => $merchant_trans_id,
                'amount' => $amount,
                'action' => $action,
                'status' => 'preparing',
                'sign_time' => $sign_time,
                'sign_string' => $sign_string
            ]
        );

        return response()->json([
            'click_trans_id' => $click_trans_id,
            'merchant_trans_id' => $merchant_trans_id,
            'merchant_prepare_id' => $merchant_trans_id,
            'error' => 0,
            'error_note' => 'Success'
        ]);
    }

    private function complete(Request $request)
    {
        $click_trans_id = $request->input('click_trans_id');
        $merchant_trans_id = $request->input('merchant_trans_id');
        $click_paydoc_id = $request->input('click_paydoc_id');
        $amount = (float)$request->input('amount');
        $error = (int)$request->input('error');
        $sign_time = $request->input('sign_time');
        $sign_string = $request->input('sign_string');
        $action = 1;

        // Verify sign
        $my_sign = md5($click_trans_id . $this->service_id . $this->secret_key . $merchant_trans_id . $click_paydoc_id . $amount . $action . $sign_time);
        if ($my_sign !== $sign_string) {
            return response()->json(['error' => -1, 'error_note' => 'SIGN CHECK FAILED']);
        }

        $payment = AcademyPayment::find($merchant_trans_id);
        if (!$payment) {
            return response()->json(['error' => -5, 'error_note' => 'TRANSACTION NOT FOUND']);
        }

        if ($error < 0) {
            $payment->update(['status' => 'failed']);
            ClickTransaction::updateOrCreate(
                ['click_trans_id' => $click_trans_id],
                ['status' => 'failed', 'error' => $error, 'error_note' => 'Payment failed at Click']
            );
            return response()->json(['error' => -9, 'error_note' => 'Canceled']);
        }

        // Check if already completed
        if ($payment->status === 'completed') {
            return response()->json([
                'click_trans_id' => $click_trans_id,
                'merchant_trans_id' => $merchant_trans_id,
                'merchant_confirm_id' => $merchant_trans_id,
                'error' => 0,
                'error_note' => 'Success (Already processed)'
            ]);
        }

        // Successfully paid
        $payment->update(['status' => 'completed', 'payment_method' => 'click']);
        ClickTransaction::updateOrCreate(
            ['click_trans_id' => $click_trans_id],
            ['status' => 'completed', 'click_paydoc_id' => $click_paydoc_id, 'error' => 0]
        );

        Log::channel('payment')->info("Payment Completed for ID: $merchant_trans_id");

        return response()->json([
            'click_trans_id' => $click_trans_id,
            'merchant_trans_id' => $merchant_trans_id,
            'merchant_confirm_id' => $merchant_trans_id,
            'error' => 0,
            'error_note' => 'Success'
        ]);
    }
    
    public function generateLink($paymentId)
    {
        $p = AcademyPayment::findOrFail($paymentId);
        $url = "https://my.click.uz/services/pay";
        $params = [
            'service_id' => $this->service_id,
            'merchant_id' => $this->merchant_id,
            'amount' => number_format($p->amount, 2, '.', ''),
            'transaction_param' => $p->id,
            'return_url' => url('/academy/dashboard')
        ];
        return $url . '?' . http_build_query($params);
    }
}
