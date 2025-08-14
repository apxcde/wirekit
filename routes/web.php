<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MagicLoginLinkController;
use App\Http\Controllers\Auth\SocialiteCallbackController;
use App\Http\Controllers\Auth\SocialiteRedirectController;

Route::get('/auth/magic-login-link', MagicLoginLinkController::class)->name('magic.login');

Route::get('/auth/{provider}/redirect', SocialiteRedirectController::class)->name('oauth.redirect');
Route::get('/auth/{provider}/callback', SocialiteCallbackController::class)->name('oauth.callback');

Route::post('logout', App\Actions\Auth\Logout::class)->name('logout');
