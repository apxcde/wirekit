<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_user_can_view_register_page()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Sign up');
    }

    public function test_user_can_view_forgot_password_page()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Forgot your password?');
    }

    public function test_authentication_works_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertTrue(Auth::attempt([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));
    }

    public function test_authentication_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertFalse(Auth::attempt([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]));
    }

    public function test_user_can_access_dashboard_when_authenticated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/app/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Good afternoon');
    }

    public function test_user_cannot_access_dashboard_when_not_authenticated()
    {
        $response = $this->get('/app/dashboard');

        $response->assertRedirect('/login');
    }
}
