<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('ping', function () {
    return response('ok', 200);
});

Route::any('auth', [AuthController::class, 'handler']);

Route::post('webhook', [WebhookController::class, 'handler']);
