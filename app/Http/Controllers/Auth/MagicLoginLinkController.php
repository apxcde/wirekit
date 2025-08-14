<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class MagicLoginLinkController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $userId = request()->query('user');

        if (! request()->hasValidSignature() || ! $user = User::find($userId)) {
            abort(403);
        }

        Auth::login($user, true);
        return redirect()->route('dashboard');
    }
}
