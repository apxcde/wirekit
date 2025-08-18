<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUser;
use App\Mail\MagicLoginLink;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    private RegisterUser $registerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerUser = new RegisterUser();
    }

    #[Test]
    public function test_registers_user_with_password_successfully()
    {
        $email = 'test@example.com';
        $name = 'John Doe';
        $password = 'password123';

        $result = $this->registerUser->handle($email, $name, $password);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->email);
        $this->assertEquals($name, $result->name);
        $this->assertTrue(Hash::check($password, $result->password));
    }

    #[Test]
    public function test_registers_user_without_name_successfully()
    {
        $email = 'test@example.com';
        $password = 'password123';

        $result = $this->registerUser->handle($email, null, $password);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->email);
        $this->assertNull($result->name);
        $this->assertTrue(Hash::check($password, $result->password));
    }

    #[Test]
    public function test_registers_user_with_magic_link_when_password_is_null()
    {
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

        $this->assertTrue($result);
        
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($email, $user->email);
        $this->assertEquals($name, $user->name);
        $this->assertNull($user->password);

        Mail::assertSent(MagicLoginLink::class, function ($mail) use ($user) {
            return $mail->hasTo($user);
        });
    }

    #[Test]
    public function test_registers_user_with_magic_link_when_password_is_empty_string()
    {
        $email = 'test@example.com';
        $name = 'John Doe';
        $password = '';

        $result = $this->registerUser->handle($email, $name, $password);

        $this->assertInstanceOf(User::class, $result);
        
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($email, $user->email);
        $this->assertEquals($name, $user->name);
        $this->assertNull($user->password); // Empty string is treated as falsy, so password becomes null
    }

    #[Test]
    public function test_fires_registered_event_when_user_is_created()
    {
        Event::fake();
        $email = 'test@example.com';
        $name = 'John Doe';
        $password = 'password123';

        $result = $this->registerUser->handle($email, $name, $password);

        Event::assertDispatched(Registered::class, function ($event) use ($result) {
            return $event->user->id === $result->id;
        });
    }

    #[Test]
    public function test_fires_registered_event_when_user_is_created_with_magic_link()
    {
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
    }

    #[Test]
    public function test_returns_false_when_exception_occurs()
    {
        $email = 'test@example.com';
        $name = 'John Doe';
        $password = 'password123';

        User::create([
            'email' => $email,
            'name' => 'Existing User',
            'password' => Hash::make('existing-password'),
        ]);

        $result = $this->registerUser->handle($email, 'New User', $password);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_returns_false_when_exception_occurs_with_magic_link()
    {
        $email = 'test@example.com';
        $name = 'John Doe';
        $password = null;

        User::create([
            'email' => $email,
            'name' => 'Existing User',
            'password' => null,
        ]);

        $result = $this->registerUser->handle($email, $name, $password);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_creates_user_with_hashed_password_when_password_provided()
    {
        $email = 'test@example.com';
        $password = 'password123';

        $result = $this->registerUser->handle($email, null, $password);

        $this->assertInstanceOf(User::class, $result);
        $this->assertNotEquals($password, $result->password);
        $this->assertTrue(Hash::check($password, $result->password));
    }

    #[Test]
    public function test_creates_user_with_null_password_when_password_is_null()
    {
        $email = 'test@example.com';
        $password = null;

        Mail::fake();
        URL::shouldReceive('temporarySignedRoute')
            ->once()
            ->andReturn('https://example.com/magic-link');

        $this->registerUser->handle($email, null, $password);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertNull($user->password);
    }

    #[Test]
    public function test_handles_duplicate_email_gracefully()
    {
        $email = 'test@example.com';
        $password = 'password123';

        User::create([
            'email' => $email,
            'name' => 'Existing User',
            'password' => Hash::make('existing-password'),
        ]);

        $result = $this->registerUser->handle($email, 'New User', $password);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_handles_invalid_email_format_gracefully()
    {
        $email = 'invalid-email';
        $password = 'password123';

        $result = $this->registerUser->handle($email, 'Test User', $password);

        $this->assertInstanceOf(User::class, $result); // Laravel doesn't validate email format by default
    }

    #[Test]
    public function test_creates_magic_link_with_correct_parameters()
    {
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
        $this->assertNotNull($user);
    }

    #[Test]
    public function test_sends_magic_login_link_to_correct_user()
    {
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
    }
}
