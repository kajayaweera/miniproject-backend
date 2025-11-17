<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChildProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SalaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::get('/get/teachers',[AuthController::class,'getTeachers']);



Route::apiResource('child-profiles', ChildProfileController::class);
Route::apiResource('payments', PaymentController::class);
Route::apiResource('salaries', SalaryController::class);


Route::get('teacher/salary/{user}',[SalaryController::class , 'getTeacherSalary'] );


Route::get('child/profile/{user}',[ChildProfileController::class , 'getChildProfile'] );

Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');