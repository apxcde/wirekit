<?php

namespace App\Actions\Auth;

use Laravel\Socialite\Facades\Socialite;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\RedirectResponse;

final class SocialiteRedirect
{
    use AsAction;

    public function asController(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }
}
