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

        $email = Str::lower(trim((string) $googleUser->getEmail()));

        if (! $email) {
            return redirect()->route('login')->with('error', 'Google did not provide a valid email address.');
        }

        $googleId = (string) $googleUser->getId();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && $user->google_id && (string) $user->google_id !== $googleId) {
            return redirect()->route('login')->with('error', 'This Google account is already linked to another user.');
        }

        if ($user && ! $user->google_id) {
            return redirect()->route('login')->with('error', 'This email address is already registered. Please use another Gmail account.');
        }

        if (! $user) {
            $user = new User([
                'email' => $email,
            ]);
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
