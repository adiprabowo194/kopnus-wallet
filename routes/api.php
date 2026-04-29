<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;

Route::prefix('wallet')->controller(WalletController::class)->group(function () {

    Route::get('{memberCode}/balance', 'balance');
    Route::get('{memberCode}/history', 'history');
    Route::post('{memberCode}/deposit', 'deposit');
    Route::post('{memberCode}/withdraw', 'withdraw');
})->middleware('throttle:globale');;
