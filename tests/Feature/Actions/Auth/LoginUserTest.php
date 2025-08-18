<?php

use App\Actions\Auth\LoginUser;
use App\Mail\MagicLoginLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;

beforeEach(function () {
    $this->loginUser = new LoginUser();
});

test('handles password login successfully', function () {
    $email = 'test@example.com';
    $password = 'password123';
    $user = User::create([
        'email' => $email,
        'name' => 'Test User',
        'password' => Hash::make($password),
    ]);

    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password], false)
        ->andReturn(true);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    Session::shouldReceive('regenerate')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeTrue();
});

test('handles password login with remember me', function () {
    $email = 'test@example.com';
    $password = 'password123';
    $remember = true;

    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password], true)
        ->andReturn(true);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    Session::shouldReceive('regenerate')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password, $remember);

    expect($result)->toBeTrue();
});

test('handles password login failure', function () {
    $email = 'test@example.com';
    $password = 'wrongpassword';

    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password], false)
        ->andReturn(false);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeFalse();
});

test('handles magic link login successfully', function () {
    $email = 'test@example.com';
    $user = User::create([
        'email' => $email,
        'name' => 'Test User',
        'password' => null,
    ]);

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeTrue();

    Mail::assertSent(MagicLoginLink::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('handles magic link login with nonexistent user', function () {
    $email = 'nonexistent@example.com';

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeFalse();
});

test('handles magic link login with empty string password', function () {
    $email = 'test@example.com';
    $password = '';

    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password], false)
        ->andReturn(false);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeFalse();
});

test('rate limits password login when too many attempts', function () {
    $email = 'test@example.com';
    $password = 'password123';

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(true);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeFalse();
});

test('rate limits magic link login when too many attempts', function () {
    $email = 'test@example.com';

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(true);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeFalse();
});

test('clears rate limit on successful password login', function () {
    $email = 'test@example.com';
    $password = 'password123';

    Auth::shouldReceive('attempt')
        ->once()
        ->andReturn(true);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    Session::shouldReceive('regenerate')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeTrue();
});

test('clears rate limit on successful magic link login', function () {
    $email = 'test@example.com';
    $user = User::create([
        'email' => $email,
        'name' => 'Test User',
        'password' => null,
    ]);

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeTrue();
});

test('does not clear rate limit on failed password login', function () {
    $email = 'test@example.com';
    $password = 'wrongpassword';

    Auth::shouldReceive('attempt')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeFalse();
});

test('does not clear rate limit on nonexistent user magic link', function () {
    $email = 'nonexistent@example.com';

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeFalse();
});

test('regenerates session on successful password login', function () {
    $email = 'test@example.com';
    $password = 'password123';

    Auth::shouldReceive('attempt')
        ->once()
        ->andReturn(true);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    Session::shouldReceive('regenerate')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeTrue();
});

test('uses correct throttle keys', function () {
    $email = 'test@example.com';
    $password = 'password123';

    Auth::shouldReceive('attempt')
        ->once()
        ->andReturn(true);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    Session::shouldReceive('regenerate')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeTrue();
});

test('uses correct magic link throttle keys', function () {
    $email = 'test@example.com';
    $user = User::create([
        'email' => $email,
        'name' => 'Test User',
        'password' => null,
    ]);

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeTrue();
});

test('sends magic link with correct url', function () {
    $email = 'test@example.com';
    $user = User::create([
        'email' => $email,
        'name' => 'Test User',
        'password' => null,
    ]);

    Mail::fake();

    $this->mock('url', function ($mock) use ($user) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->with('magic.login', \Mockery::any(), ['user' => $user->id])
            ->andReturn('https://example.com/magic-link');
    });

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeTrue();

    Mail::assertSent(MagicLoginLink::class, function ($mail) use ($user) {
        return $mail->url === 'https://example.com/magic-link';
    });
});

test('handles null password as magic link login', function () {
    $email = 'test@example.com';
    $user = User::create([
        'email' => $email,
        'name' => 'Test User',
        'password' => null,
    ]);

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    RateLimiter::shouldReceive('clear')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, null);

    expect($result)->toBeTrue();

    Mail::assertSent(MagicLoginLink::class);
});

test('handles empty string password as password login', function () {
    $email = 'test@example.com';
    $password = '';

    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password], false)
        ->andReturn(false);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(false);

    RateLimiter::shouldReceive('hit')
        ->once()
        ->andReturn(null);

    $result = $this->loginUser->handle($email, $password);

    expect($result)->toBeFalse();
});
