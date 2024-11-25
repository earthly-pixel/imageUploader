<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/upload', [ApiController::class, 'upload']);
Route::get('/image/all', [ApiController::class, 'getAllPhoto']);
Route::get('/video/all', [ApiController::class, 'getAllVideo']);
