<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\MagicLoginLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

final class LoginUser
{
    use AsAction;

    public function handle(string $email, ?string $password = null, bool $remember = false): bool
    {
        if (!$password) {
            return $this->handleWithMagicLink($email);
        }

        return $this->handleWithPassword($email, $password, $remember);
    }

    private function handleWithPassword(string $email, string $password, bool $remember): bool
    {
        $throttleKey = 'login.' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return false;
        }

        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            session()->regenerate();
            return true;
        }

        return false;
    }

    private function handleWithMagicLink(string $email): bool
    {
        $user = User::firstWhere('email', $email);

        if (!$user) {
            return false;
        }

        $url = URL::temporarySignedRoute(
            'magic.login',
            now()->addMinutes(30),
            [ 'user' => $user->id ]
        );

        Mail::to($user)->send(new MagicLoginLink($url));

        return true;
    }
}
