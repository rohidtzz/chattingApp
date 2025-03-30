<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/chat/{id}', [App\Http\Controllers\Api\Chat\MessageController::class, 'store']);

Route::get('/chat/{receiver_id}/{sender_id}', [App\Http\Controllers\Api\Chat\MessageController::class, 'index']);

