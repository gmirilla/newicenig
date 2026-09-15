<?php

use App\Http\Controllers\Portal\CertificateController;
use App\Http\Controllers\Portal\MemberDocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('member')->name('member.')->middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('renew', 'portal.renew')->name('renew');
    Route::view('change-level', 'portal.change-level')->name('change-level');
    Route::view('notices', 'portal.notices')->name('notices');
    Route::view('documents', 'portal.documents')->name('documents');
    Route::get('documents/{document}/download', [MemberDocumentController::class, 'show'])->name('documents.download');
    Route::get('certificate', [CertificateController::class, 'show'])->name('certificate');
});
