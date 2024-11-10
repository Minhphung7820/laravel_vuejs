<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileUploadController;

Route::post('/upload-image', [FileUploadController::class, 'uploadImage'])->name('upload.image');
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('get-profile', [AuthController::class, 'getProfile'])->middleware('auth:api');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::apiResource('products', ProductController::class)->middleware('auth:api');
