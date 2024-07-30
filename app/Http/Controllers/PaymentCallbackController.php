<?php

namespace App\Http\Controllers;

use App\Jobs\AddUserCredit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\InvoiceCallback;

class PaymentCallbackController extends Controller
{
    protected InvoiceApi $invoice;

    public function __construct()
    {
        $apiInstance = new InvoiceApi;

        $this->invoice = $apiInstance;
    }

    public function xenInvoice(Request $request): \Illuminate\Http\JsonResponse
    {
        // validate webhook id
        $header = $request->header('x-callback-token');

        if ($header !== env('XENDIT_WEBHOOK_KEY')) {
            return response()->json('Invalid webhook key', 400);
        }

        $callback = new InvoiceCallback($request->all());

        $transaction = Transaction::where('order_id', $callback->getExternalId())->first();

        if ($transaction) {
            $transaction->status = $callback->getStatus();
            $transaction->save();

            // update point
            $credits = $transaction->credits;
            AddUserCredit::dispatchIf($transaction->status === 'PAID', $transaction->user, $credits);

            return response()->json(['success' => true]);

        }

        return response()->json(['success' => false, 'message' => 'Invalid order id'], 400);
    }
}
