<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use LemonSqueezy\Laravel\Billable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use Billable;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function initials(): string
    {
        if ($this->name) {
            return Str::of($this->name)
                ->explode(' ')
                ->map(fn (string $name) => Str::of($name)->substr(0, 1)->upper())
                ->implode('');
        }

        return Str::of($this->email)
            ->substr(0, 2)
            ->upper()
            ->toString();
    }
}
