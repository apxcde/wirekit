<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

final class MagicLoginLink
{
    use AsAction;

    public function asController(Request $request): RedirectResponse
    {
        $userId = request()->query('user');

        if (! request()->hasValidSignature() || ! $user = User::find($userId)) {
            abort(403);
        }

        Auth::login($user, true);
        return redirect()->route('dashboard');
    }
}
