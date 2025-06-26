<?php

use Illuminate\Support\Facades\Route;
use App\Actions\Auth\MagicLoginLink;
use App\Actions\Auth\SocialiteRedirect;
use App\Actions\Auth\SocialiteCallback;

Route::get('/auth/magic-login-link', MagicLoginLink::class)->name('magic.login');

Route::get('/auth/{provider}/redirect', SocialiteRedirect::class)->name('oauth.redirect');
Route::get('/auth/{provider}/callback', SocialiteCallback::class)->name('oauth.callback');

Route::post('logout', App\Actions\Auth\Logout::class)->name('logout');
