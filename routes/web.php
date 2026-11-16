<?php

use App\Infrastructure\Http\Controllers\Web\ContestRegistrationController;
use App\Infrastructure\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('contests/{contest}/iscrizione', [ContestRegistrationController::class, 'create'])
    ->name('contests.registration.create');
Route::post('contests/{contest}/iscrizione', [ContestRegistrationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contests.registration.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
