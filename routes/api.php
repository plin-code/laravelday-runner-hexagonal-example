<?php

use App\Infrastructure\Http\Controllers\Api\CheckInController;
use Illuminate\Support\Facades\Route;

Route::post('contests/{contest}/runners/{runner}/check-in', CheckInController::class)
    ->middleware('auth:sanctum')
    ->can('checkIn', 'contest')
    ->name('api.contests.runners.check-in');
