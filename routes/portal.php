<?php

use App\Http\Controllers\Portal\CertificateController;
use Illuminate\Support\Facades\Route;

Route::prefix('member')->name('member.')->middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('renew', 'portal.renew')->name('renew');
    Route::get('certificate', [CertificateController::class, 'show'])->name('certificate');
});
