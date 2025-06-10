<?php

use Illuminate\Support\Facades\Route;
use App\Actions\Auth\MagicLoginLink;

Route::get('/auth/magic-login-link', MagicLoginLink::class)->name('magic.login');

Route::post('logout', App\Actions\Auth\Logout::class)
    ->name('logout');
