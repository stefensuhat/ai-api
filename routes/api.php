<?php

use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\PricingPlanController;
use App\Http\Controllers\TransactionController;
use App\Models\Result;
use Illuminate\Support\Facades\Route;

Route::get('/api', function () {
    $image = Result::whereNotNull('success_response')->get();

    return $image;
});

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/provider/callback', [AuthController::class, 'loginWithProvider']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::post('generate-image', [ImageController::class, 'generateImage']);

    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::post('checkout', [TransactionController::class, 'store']);
});
Route::post('xendit/invoices/callback', [PaymentCallbackController::class, 'xenInvoice']);

Route::apiResource('pricing', PricingPlanController::class);
Route::get('ai-models', [AiModelController::class, 'index']);
Route::post('save-image', [ImageController::class, 'saveImage']);
Route::get('get-image', [ImageController::class, 'getImage']);
Route::get('store-image', [ImageController::class, 'storeImage']);
