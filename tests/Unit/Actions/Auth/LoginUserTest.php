<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LoginUser;
use App\Mail\MagicLoginLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    private LoginUser $loginUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser = new LoginUser();
    }

    #[Test]
    public function test_handles_password_login_successfully()
    {
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

        $this->assertTrue($result);
    }

    #[Test]
    public function test_handles_password_login_with_remember_me()
    {
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

        $this->assertTrue($result);
    }

    #[Test]
    public function test_handles_password_login_failure()
    {
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

        $this->assertFalse($result);
    }

    #[Test]
    public function test_handles_magic_link_login_successfully()
    {
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

        $this->assertTrue($result);

        Mail::assertSent(MagicLoginLink::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    #[Test]
    public function test_handles_magic_link_login_with_nonexistent_user()
    {
        $email = 'nonexistent@example.com';

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(false);

        RateLimiter::shouldReceive('hit')
            ->once()
            ->andReturn(null);

        $result = $this->loginUser->handle($email, null);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_handles_magic_link_login_with_empty_string_password()
    {
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

        $this->assertFalse($result);
    }

    #[Test]
    public function test_rate_limits_password_login_when_too_many_attempts()
    {
        $email = 'test@example.com';
        $password = 'password123';

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(true);

        $result = $this->loginUser->handle($email, $password);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_rate_limits_magic_link_login_when_too_many_attempts()
    {
        $email = 'test@example.com';

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(true);

        $result = $this->loginUser->handle($email, null);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_clears_rate_limit_on_successful_password_login()
    {
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

        $this->assertTrue($result);
    }

    #[Test]
    public function test_clears_rate_limit_on_successful_magic_link_login()
    {
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

        $this->assertTrue($result);
    }

    #[Test]
    public function test_does_not_clear_rate_limit_on_failed_password_login()
    {
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

        $this->assertFalse($result);
    }

    #[Test]
    public function test_does_not_clear_rate_limit_on_nonexistent_user_magic_link()
    {
        $email = 'nonexistent@example.com';

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(false);

        RateLimiter::shouldReceive('hit')
            ->once()
            ->andReturn(null);

        $result = $this->loginUser->handle($email, null);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_regenerates_session_on_successful_password_login()
    {
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

        $this->assertTrue($result);
    }

    #[Test]
    public function test_uses_correct_throttle_keys()
    {
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

        $this->assertTrue($result);
    }

    #[Test]
    public function test_uses_correct_magic_link_throttle_keys()
    {
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

        $this->assertTrue($result);
    }

    #[Test]
    public function test_sends_magic_link_with_correct_url()
    {
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

        $this->assertTrue($result);

        Mail::assertSent(MagicLoginLink::class, function ($mail) use ($user) {
            return $mail->url === 'https://example.com/magic-link';
        });
    }

    #[Test]
    public function test_handles_null_password_as_magic_link_login()
    {
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

        $this->assertTrue($result);

        Mail::assertSent(MagicLoginLink::class);
    }

    #[Test]
    public function test_handles_empty_string_password_as_password_login()
    {
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

        $this->assertFalse($result);
    }
}
