<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse;

class SocialiteRedirectController extends Controller
{
    public function __invoke(Request $request, string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }
}
