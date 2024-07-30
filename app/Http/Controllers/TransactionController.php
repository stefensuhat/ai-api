<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class TransactionController extends Controller
{
    protected InvoiceApi $invoice;

    public function __construct()
    {
        $apiInstance = new InvoiceApi;

        $this->invoice = $apiInstance;
    }

    public function show(string $transaction_id)
    {
        $transaction = Transaction::ofOrder($transaction_id)->first();

        try {
            Gate::authorize('view', $transaction);
            $resource = new TransactionResource($transaction);

            return response()->json($resource);

        } catch (\Throwable $th) {
            return response()->json(['error' => true, 'data' => $th->getMessage()], 400);
        }

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'credits' => 'required|numeric|min:100',
        ]);

        $user = $request->user();

        if ($validated) {
            DB::beginTransaction();
            try {
                $getPricePerCredit = Setting::where('key', 'pricePerCredit')->value('value');
                $price = $request->input('credits') * $getPricePerCredit;

                $transaction = new Transaction;
                $transaction->user()->associate($user);
                $transaction->order_id = $this->generateTrxId();
                $transaction->credits = $request->input('credits');
                $transaction->subtotal = $price;
                $transaction->grand_total = $price;
                $transaction->status = 'PENDING';
                $transaction->save();

                // create invoice for the transaction
                $redirectQueryString = '?transactionId='.$transaction->order_id;
                $invoiceRequest = new CreateInvoiceRequest([
                    'external_id' => $transaction->order_id,
                    'description' => 'Credit purchase',
                    'amount' => $transaction->grand_total,
                    'currency' => 'IDR',
                    'reminder_time' => 1,
                    'success_redirect_url' => env('WEB_URL').'/checkout/success'.$redirectQueryString,
                    'failure_redirect_url' => env('WEB_URL').'/checkout/failed'.$redirectQueryString,
                ]);

                try {
                    $result = $this->invoice->createInvoice($invoiceRequest);

                    $payment = new Payment;
                    $payment->transaction()->associate($transaction);
                    $payment->payment_invoice_id = $result->getId();
                    $payment->save();

                    DB::commit();

                    $response = ['invoice_url' => $result->getInvoiceUrl(), 'transaction' => $transaction];

                    return response()->json($response);
                } catch (\Xendit\XenditSdkException $e) {
                    DB::rollBack();
                    logger()->error(json_encode($e->getFullError()));

                    return response()->json($e->getMessage(), 500);
                }
            } catch (\Exception $e) {
                DB::rollBack();

                logger()->error(json_encode($e->getMessage()));

                return response()->json($e->getMessage(), 500);
            }

        }

        return response()->json('Failed to create transaction', 400);
    }

    protected function generateTrxId(): string
    {
        $dateNow = Carbon::now()->format('Ymd');
        $randomString = Str::random(10);

        $orderId = $dateNow.$randomString;

        if (Transaction::ofOrder($orderId)->exists()) {
            return $this->generateTrxId();
        }

        return $orderId;
    }
}
