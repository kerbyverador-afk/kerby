<?php

namespace Tests\Feature\Auth;

use App\Models\User;
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
        $response->assertSee('Continue with Google');
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

    public function test_google_sign_in_rejects_an_email_already_used_by_a_local_account(): void
    {
        $user = User::factory()->create([
            'email' => 'registered@example.com',
            'role' => 'tenant',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn(SocialiteUser::fake([
                'id' => 'google-user-456',
                'name' => 'Google User',
                'email' => 'registered@example.com',
            ]));

        $response = $this->get('/auth/google/callback');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'This email address is already registered. Please use another Gmail account.');
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_google_sign_in_rejects_an_email_already_used_by_an_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn(SocialiteUser::fake([
                'id' => 'google-admin-456',
                'name' => 'Google Admin',
                'email' => 'admin@example.com',
            ]));

        $response = $this->get('/auth/google/callback');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'This email address is already registered. Please use another Gmail account.');
        $this->assertNull($admin->fresh()->google_id);
    }

    public function test_existing_google_account_can_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'google.user@example.com',
            'role' => 'tenant',
            'google_id' => 'google-user-123',
        ]);

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

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
