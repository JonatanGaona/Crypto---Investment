<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoController;

Route::get('/', [CryptoController::class, 'index']);

Route::get('/api/crypto/updates', [CryptoController::class, 'getUpdates']);