<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Lorisleiva\Actions\Concerns\AsAction;

final class SocialiteCallback
{
    use AsAction;

    public function asController(string $provider): RedirectResponse
    {
        dump($provider);

        $socialUser = Socialite::driver($provider)->user();

        dump($socialUser);

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
