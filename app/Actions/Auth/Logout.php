<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class Logout
{
    public function __invoke(): void
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
