<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('Terms & Conditions')
            ->assertSee('I have read and agree to the Terms & Conditions.')
            ->assertSee('name="terms" value="1"', false)
            ->assertSee('Create Account & Continue')
            ->assertSee('Account Responsibilities');
    }

    public function test_home_page_has_english_copy_and_terms_and_conditions(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('A simple way to track your monthly')
            ->assertSee('Terms & Conditions')
            ->assertDontSee('Ligtas at madaling paraan');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_users_must_accept_the_terms_before_registering(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('terms');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
