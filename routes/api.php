<?php

use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatGroupController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\PricingPlanController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/api', function () {
    $guest = auth()->guest();

    return $guest;
});

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/provider/callback', [AuthController::class, 'loginWithProvider']);
Route::get('ai-models', [AiModelController::class, 'index']);
Route::apiResource('pricing', PricingPlanController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);

    Route::post('text-to-image', [ImageController::class, 'textToImage']);
    Route::post('text-to-image/{textToImage}/results', [ImageController::class, 'getImageResult']);

    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::post('checkout', [TransactionController::class, 'store']);

    Route::get('chat-groups', [ChatGroupController::class, 'index']);
    Route::get('chats', [ChatController::class, 'index']);
    Route::post('chat/save', [ChatController::class, 'store']);
});

// payment gateway callback
Route::post('xendit/invoices/callback', [PaymentCallbackController::class, 'xenInvoice']);
