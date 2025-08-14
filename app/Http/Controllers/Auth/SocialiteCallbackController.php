<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialiteCallbackController extends Controller
{
    public function __invoke(Request $request, string $provider): RedirectResponse
    {
        $socialUser = Socialite::driver($provider)->user();

        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                $provider . '_id' => $socialUser->getId(),
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }
}
