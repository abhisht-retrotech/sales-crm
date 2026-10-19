<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', function () {
    return response()->json([
        'status' => 'online',
        'framework' => 'Laravel ' . app()->version(),
        'app' => 'DealSync Enterprise CRM',
        'timestamp' => now()->toIso8601String(),
    ]);
});

