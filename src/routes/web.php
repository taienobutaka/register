<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', function () {
    return view('welcome');
});

// 会員登録画面
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');

// 会員登録処理
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
