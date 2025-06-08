<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Log;
use App\Mail\MagicLoginLink;

final class LoginOrRegisterUser
{
    use AsAction;

    public function handle(string $email): bool
    {
        try {
            $user = User::firstOrCreate([ 'email' => $email ]);

            $url = URL::temporarySignedRoute(
                'magic.login',
                now()->addMinutes(30),
                [ 'user' => $user->id ]
            );

            Mail::to($user)->send(new MagicLoginLink($url));

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }
}
