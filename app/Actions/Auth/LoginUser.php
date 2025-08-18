<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\MagicLoginLink;

final class LoginUser
{
    use AsAction;

    public function handle(string $email): bool
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
