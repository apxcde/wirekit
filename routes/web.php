<?php

use Illuminate\Support\Facades\Route;
use App\Actions\Auth\MagicLoginLink;

Route::get('/auth/magic-login-link', MagicLoginLink::class)->name('magic.login');

Route::get('/auth/{provider}/redirect', App\Actions\Auth\SocialiteRedirect::class)
    ->name('oauth.redirect');

Route::get('/auth/{provider}/callback', App\Actions\Auth\SocialiteCallback::class)
    ->name('oauth.callback');

Route::post('logout', App\Actions\Auth\Logout::class)
    ->name('logout');
