<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_displays_google_sign_in_button(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login with Google');
    }

    public function test_google_sign_in_prompts_user_to_choose_an_account(): void
    {
        $response = $this->get('/auth/google');

        $response->assertRedirect();
        $this->assertStringContainsString('prompt=select_account', $response->headers->get('Location'));
    }

    public function test_users_can_authenticate_with_google(): void
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn(SocialiteUser::fake([
                'id' => 'google-user-123',
                'name' => 'Google User',
                'email' => 'google.user@example.com',
            ]));

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'google.user@example.com',
            'google_id' => 'google-user-123',
        ]);
    }
}
