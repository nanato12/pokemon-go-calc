<?php

use App\Http\Controllers\Api\LineBotController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', [LineBotController::class, 'callback']);
