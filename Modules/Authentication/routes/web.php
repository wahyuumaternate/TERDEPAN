<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\AuthenticationController;

Route::get('/login', [AuthenticationController::class, 'index'])->name('login');
