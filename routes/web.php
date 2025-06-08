<?php

use Illuminate\Support\Facades\Route;
use App\Actions\Auth\MagicLoginLink;

Route::get('/auth/magic-login-link', MagicLoginLink::class)->name('magic.login');
