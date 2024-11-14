<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileUploadController;
// routes/api.php
Route::post('/send-message', [ChatController::class, 'sendMessage'])->middleware('auth:api');
Route::post('/upload-image', [FileUploadController::class, 'uploadImage'])->name('upload.image');
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('get-profile', [AuthController::class, 'getProfile'])->middleware('auth:api');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::apiResource('products', ProductController::class)->middleware('auth:api');
