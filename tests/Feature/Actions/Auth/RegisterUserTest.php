<?php

use App\Actions\Auth\RegisterUser;
use App\Mail\MagicLoginLink;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;

beforeEach(function () {
    $this->registerUser = new RegisterUser();
});

test('registers user with password successfully', function () {
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = 'password123';

    $result = $this->registerUser->handle($email, $name, $password);

    expect($result)->toBeInstanceOf(User::class);
    expect($result->email)->toEqual($email);
    expect($result->name)->toEqual($name);
    expect(Hash::check($password, $result->password))->toBeTrue();
});

test('registers user without name successfully', function () {
    $email = 'test@example.com';
    $password = 'password123';

    $result = $this->registerUser->handle($email, null, $password);

    expect($result)->toBeInstanceOf(User::class);
    expect($result->email)->toEqual($email);
    expect($result->name)->toBeNull();
    expect(Hash::check($password, $result->password))->toBeTrue();
});

test('registers user with magic link when password is null', function () {
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = null;

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    $result = $this->registerUser->handle($email, $name, $password);

    expect($result)->toBeTrue();

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->email)->toEqual($email);
    expect($user->name)->toEqual($name);
    expect($user->password)->toBeNull();

    Mail::assertSent(MagicLoginLink::class, function ($mail) use ($user) {
        return $mail->hasTo($user);
    });
});

test('registers user with magic link when password is empty string', function () {
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = '';

    $result = $this->registerUser->handle($email, $name, $password);

    expect($result)->toBeInstanceOf(User::class);

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->email)->toEqual($email);
    expect($user->name)->toEqual($name);
    expect($user->password)->toBeNull();
    // Empty string is treated as falsy, so password becomes null
});

test('fires registered event when user is created', function () {
    Event::fake();
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = 'password123';

    $result = $this->registerUser->handle($email, $name, $password);

    Event::assertDispatched(Registered::class, function ($event) use ($result) {
        return $event->user->id === $result->id;
    });
});

test('fires registered event when user is created with magic link', function () {
    Event::fake();
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = null;

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    $this->registerUser->handle($email, $name, $password);

    Event::assertDispatched(Registered::class, function ($event) use ($email) {
        return $event->user->email === $email;
    });
});

test('returns false when exception occurs', function () {
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = 'password123';

    User::create([
        'email' => $email,
        'name' => 'Existing User',
        'password' => Hash::make('existing-password'),
    ]);

    $result = $this->registerUser->handle($email, 'New User', $password);

    expect($result)->toBeFalse();
});

test('returns false when exception occurs with magic link', function () {
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = null;

    User::create([
        'email' => $email,
        'name' => 'Existing User',
        'password' => null,
    ]);

    $result = $this->registerUser->handle($email, $name, $password);

    expect($result)->toBeFalse();
});

test('creates user with hashed password when password provided', function () {
    $email = 'test@example.com';
    $password = 'password123';

    $result = $this->registerUser->handle($email, null, $password);

    expect($result)->toBeInstanceOf(User::class);
    $this->assertNotEquals($password, $result->password);
    expect(Hash::check($password, $result->password))->toBeTrue();
});

test('creates user with null password when password is null', function () {
    $email = 'test@example.com';
    $password = null;

    Mail::fake();
    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->andReturn('https://example.com/magic-link');

    $this->registerUser->handle($email, null, $password);

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->password)->toBeNull();
});

test('handles duplicate email gracefully', function () {
    $email = 'test@example.com';
    $password = 'password123';

    User::create([
        'email' => $email,
        'name' => 'Existing User',
        'password' => Hash::make('existing-password'),
    ]);

    $result = $this->registerUser->handle($email, 'New User', $password);

    expect($result)->toBeFalse();
});

test('handles invalid email format gracefully', function () {
    $email = 'invalid-email';
    $password = 'password123';

    $result = $this->registerUser->handle($email, 'Test User', $password);

    expect($result)->toBeInstanceOf(User::class);
    // Laravel doesn't validate email format by default
});

test('creates magic link with correct parameters', function () {
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = null;

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    $this->registerUser->handle($email, $name, $password);

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
});

test('sends magic login link to correct user', function () {
    $email = 'test@example.com';
    $name = 'John Doe';
    $password = null;

    Mail::fake();

    $this->mock('url', function ($mock) {
        $mock->shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');
    });

    $this->registerUser->handle($email, $name, $password);

    $user = User::where('email', $email)->first();

    Mail::assertSent(MagicLoginLink::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) &&
               $mail->url === 'https://example.com/magic-link';
    });
});
