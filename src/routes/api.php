<?php

declare(strict_types=1);

use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\ResetController;
use Illuminate\Support\Facades\Route;

Route::post('/lottery', [LotteryController::class, 'handle']);
Route::post('/reset', [ResetController::class, 'handle']);
