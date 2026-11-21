<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        /** @var GoogleProvider $googleProvider */
        $googleProvider = Socialite::driver('google');

        return $googleProvider
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            return redirect()->route('login')->with('error', 'Google sign-in session expired. Please try again.');
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Unable to sign in with Google right now. Please try again.');
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->with('error', 'Google did not provide a valid email address.');
        }

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->password = Hash::make(Str::random(40));
            $user->role = 'tenant';
            $user->email_verified_at = now();
        }

        $user->name = $user->name ?: ($googleUser->getName() ?: 'Google User');
        $user->google_id = $googleUser->getId();
        $user->email_verified_at ??= now();
        $user->save();

        Auth::login($user, true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
