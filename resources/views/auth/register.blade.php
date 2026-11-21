<x-guest-layout>
    <div class="mb-6 text-center">
        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#73917c]">New account</p>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-[#173f35]">Create your account</h2>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div id="terms-agreement" class="mt-5">
            <details class="terms-details rounded-xl p-4" open>
                <summary>Terms &amp; Conditions</summary>
                <p class="mt-2 text-sm leading-6 text-[#5f756c]">Please read these rules before you create your account.</p>
                <x-terms-and-conditions />
            </details>
            <label for="terms" class="mt-4 flex items-start gap-3 text-sm leading-6 text-[#687d72]">
                <input id="terms" type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} class="mt-1 rounded border-[#b9c8be] text-[#173f35] focus:ring-[#73917c]" required>
                <span>I have read and agree to the Terms &amp; Conditions.</span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6">
        <a href="{{ route('auth.google') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path d="M21.6 12.23c0-.7-.06-1.38-.17-2.03H12v3.84h5.39a4.61 4.61 0 0 1-2 3.01v2.5h3.24c1.9-1.75 2.97-4.34 2.97-7.32Z" fill="#4285F4"/>
                <path d="M12 22c2.7 0 4.96-.9 6.61-2.44l-3.24-2.5c-.9.6-2.06.96-3.37.96-2.59 0-4.79-1.75-5.57-4.11H.85v2.62A10 10 0 0 0 12 22Z" fill="#34A853"/>
                <path d="M6.43 19.82A6 6 0 0 1 6 17.2V14.6H2.66A10 10 0 0 0 2 12c0-1.6.38-3.12 1.06-4.46L6.43 10.1v9.72Z" fill="#FBBC05"/>
                <path d="M12 3.96c1.47 0 2.8.5 3.85 1.49l2.88-2.88A9.91 9.91 0 0 0 12 2a10 10 0 0 0-9.14 5.54L6.43 10.1A6 6 0 0 1 12 3.96Z" fill="#EA4335"/>
            </svg>
            {{ __('Sign up with Google') }}
        </a>
    </div>

</x-guest-layout>
