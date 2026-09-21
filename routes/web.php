<?php

use App\Http\Controllers\Admin\PaymentProofController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\Marketing\ContactController;
use App\Http\Controllers\Marketing\DownloadController;
use App\Http\Controllers\Marketing\EventController;
use App\Http\Controllers\Marketing\PageController;
use App\Http\Controllers\Marketing\PostController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('membership', [PageController::class, 'membership'])->name('membership');
Route::get('diaspora', [PageController::class, 'diaspora'])->name('diaspora');

Route::middleware('auth')->group(function () {
    Route::controller(PageController::class)->group(function () {
        Route::get('about', 'about')->name('about');
        Route::get('leadership', 'leadership')->name('leadership');
    });

    Route::controller(PostController::class)->group(function () {
        Route::get('news', 'index')->name('news.index');
        Route::get('news/{post}', 'show')->name('news.show');
    });

    Route::controller(EventController::class)->group(function () {
        Route::get('events', 'index')->name('events.index');
        Route::get('events/{event}', 'show')->name('events.show');
    });

    Route::get('resources', [DownloadController::class, 'index'])->name('resources.index');
});

Route::get('contact', [ContactController::class, 'create'])
    ->middleware('throttle:20,1')
    ->name('contact');

Route::view('join', 'marketing.join')->name('join');
Route::view('join/{tier}', 'marketing.join')->name('join.tier');

// Public renewal entry point — works for logged-in members and for members
// with no account (or an unclaimed one), who verify by membership number +
// email instead. See App\Livewire\Portal\RenewalFlow.
Route::view('renew-icen-membership', 'marketing.renew')->name('renew.lookup');

Route::get('set-password/{user}', [SetPasswordController::class, 'create'])
    ->middleware('signed')
    ->name('password.set');

Route::get('admin/payments/{payment}/proof', [PaymentProofController::class, 'show'])
    ->middleware('auth')
    ->name('payments.proof');

require __DIR__.'/payments.php';
require __DIR__.'/portal.php';
require __DIR__.'/auth.php';
