<?php

use App\Http\Controllers\Payments\PaymentController;
use Illuminate\Support\Facades\Route;

Route::controller(PaymentController::class)->prefix('payments')->name('payments.')->group(function () {
    Route::get('callback', 'callback')->name('callback');
    Route::get('success', 'success')->name('success');
    Route::post('webhook', 'webhook')->name('webhook')->middleware('paystack.signature');
});
