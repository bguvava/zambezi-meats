<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Password reset route for email notifications
Route::get('/password/reset/{token}', function () {
    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/reset-password?token=' . request('token') . '&email=' . request('email'));
})->name('password.reset');
