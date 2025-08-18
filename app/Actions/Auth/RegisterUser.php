<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Log;
use App\Mail\MagicLoginLink;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

final class RegisterUser
{
    use AsAction;

    public function handle(string $email, ?string $name = null, ?string $password = null): User|bool
    {
        try {
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $password ? Hash::make($password) : null,
            ];
            event(new Registered(($user = User::create($userData))));

            if ($password === null) {
                $url = URL::temporarySignedRoute(
                    'magic.login',
                    now()->addMinutes(30),
                    [ 'user' => $user->id ]
                );

                Mail::to($user)->send(new MagicLoginLink($url));
                return true;
            }

            return $user;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }
}
