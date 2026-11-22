<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#173f35">

        <title>Log in | Boarding House Payment System</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans text-[#18332d] antialiased">
        <main class="login-page relative grid min-h-screen overflow-hidden lg:grid-cols-[0.92fr_1.08fr]">
            <section class="login-story relative hidden overflow-hidden bg-[#173f35] px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="story-glow" aria-hidden="true"></div>

                <a href="/" class="relative z-10 flex items-center gap-3" aria-label="Boarding House home">
                    <span class="brand-mark">
                        <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                            <path d="M8 21.5 24 9l16 12.5v17H8v-17Z" fill="#f4ca64"/>
                            <path d="M17 38V28h14v10M12 21.5V14h6v3" stroke="#173f35" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="24" cy="20" r="3" fill="#fffaf0"/>
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-extrabold tracking-[0.13em]">BHOUSE</span>
                        <span class="block text-[10px] font-bold uppercase tracking-[0.24em] text-[#c6d7be]">Payment System</span>
                    </span>
                </a>

                <div class="relative z-10 max-w-lg">
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-[#dceacb]">
                        <span class="h-2 w-2 rounded-full bg-[#efc86a]"></span>
                        Welcome back
                    </div>
                    <h1 class="text-5xl font-extrabold leading-[1.02] tracking-[-0.04em]">
                        Your home.<br>
                        <span class="text-[#efc86a]">Your payment status.</span>
                    </h1>
                    <p class="mt-5 max-w-md text-base leading-7 text-[#d8e3d6]">
                        Log in to view your monthly payment, payment history, and boarding house updates.
                    </p>
                </div>

                <div class="relative z-10 mt-auto w-full max-w-xl self-center">
                    <x-boarding-payment-illustration />
                </div>
            </section>

            <section class="login-form-panel flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
                <div class="login-card w-full max-w-md">
                    <a href="/" class="mobile-brand mb-8 flex items-center gap-3 lg:hidden" aria-label="Boarding House home">
                        <span class="brand-mark">
                            <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                <path d="M8 21.5 24 9l16 12.5v17H8v-17Z" fill="#f4ca64"/>
                                <path d="M17 38V28h14v10M12 21.5V14h6v3" stroke="#173f35" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-extrabold tracking-[0.13em] text-[#173f35]">BHOUSE</span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.24em] text-[#73917c]">Payment System</span>
                        </span>
                    </a>

                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#73917c]">Account access</p>
                        <h2 class="mt-2 text-4xl font-extrabold tracking-[-0.04em] text-[#173f35]">Welcome back</h2>
                        <p class="mt-3 text-sm leading-6 text-[#718278]">Enter your details to continue to your payment dashboard.</p>
                    </div>

                    @if (session('error'))
                        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-8">
                        @csrf

                        <div>
                            <label for="email" class="text-sm font-bold text-[#31584b]">Email address</label>
                            <div class="input-wrap mt-2">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3.5 6.5h17v11h-17v-11Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5">
                            <label for="password" class="text-sm font-bold text-[#31584b]">Password</label>
                            <div class="input-wrap mt-2">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <circle cx="12" cy="15" r="1.5" fill="currentColor"/>
                                </svg>
                                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-4">
                            <label for="remember_me" class="flex items-center gap-2 text-sm font-semibold text-[#687d72]">
                                <input id="remember_me" type="checkbox" name="remember" class="remember-box">
                                Remember me
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-bold text-[#31584b] underline decoration-[#a9baad] underline-offset-4 hover:text-[#173f35]">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="login-button mt-7">
                            Log in
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>

                    <div class="my-6 flex items-center gap-3">
                        <span class="h-px flex-1 bg-[#dbe5dc]"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#91a097]">or</span>
                        <span class="h-px flex-1 bg-[#dbe5dc]"></span>
                    </div>

                    <a href="{{ route('auth.google') }}" class="google-button">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M21.6 12.23c0-.7-.06-1.38-.17-2.03H12v3.84h5.39a4.61 4.61 0 0 1-2 3.01v2.5h3.24c1.9-1.75 2.97-4.34 2.97-7.32Z" fill="#4285F4"/>
                            <path d="M12 22c2.7 0 4.96-.9 6.61-2.44l-3.24-2.5c-.9.6-2.06.96-3.37.96-2.59 0-4.79-1.75-5.57-4.11H.85v2.62A10 10 0 0 0 12 22Z" fill="#34A853"/>
                            <path d="M6.43 13.91A6 6 0 0 1 6 12c0-.66.11-1.3.32-1.9H2.66A10 10 0 0 0 2 12c0 1.6.38 3.12 1.06 4.46l3.37-2.55Z" fill="#FBBC05"/>
                            <path d="M12 3.96c1.47 0 2.8.5 3.85 1.49l2.88-2.88A9.91 9.91 0 0 0 12 2a10 10 0 0 0-9.14 5.54L6.43 10.1A6 6 0 0 1 12 3.96Z" fill="#EA4335"/>
                        </svg>
                        Continue with Google
                    </a>

                    @if (Route::has('register'))
                        <p class="mt-7 text-center text-sm text-[#718278]">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-extrabold text-[#31584b] underline decoration-[#a9baad] underline-offset-4">Create account</a>
                        </p>
                    @endif

                </div>
            </section>
        </main>

        <style>
            .login-page {
                background: #f4f7ef;
            }
            .login-form-panel {
                background:
                    radial-gradient(circle at 90% 10%, rgba(217, 167, 70, .12), transparent 20%),
                    linear-gradient(135deg, #f8faf5 0%, #eef3e9 100%);
            }
            .login-card {
                border: 1px solid rgba(191, 205, 194, .72);
                border-radius: 24px;
                background: rgba(255, 255, 255, .96);
                padding: 34px;
                box-shadow: 0 24px 70px rgba(52, 83, 67, .12);
            }
            .brand-mark {
                display: grid;
                height: 48px;
                width: 48px;
                flex: none;
                place-items: center;
                border-radius: 15px;
                background: #173f35;
                box-shadow: 0 10px 24px rgba(23, 63, 53, .16);
            }
            .brand-mark svg { width: 38px; height: 38px; }
            .mobile-brand .brand-mark { background: #173f35; }
            .story-glow {
                position: absolute;
                right: -140px;
                bottom: -140px;
                width: 420px;
                height: 420px;
                border: 70px solid rgba(239, 200, 106, .06);
                border-radius: 9999px;
            }
            .story-house {
                height: 220px;
                margin: -20px -20px -40px;
            }
            .story-house svg { width: 100%; height: 100%; }
            .input-wrap {
                display: flex;
                align-items: center;
                height: 52px;
                gap: 10px;
                border: 1px solid #cfdbd1;
                border-radius: 12px;
                background: #fff;
                padding: 0 14px;
                color: #84978d;
                transition: border-color .18s ease, box-shadow .18s ease;
            }
            .input-wrap:focus-within {
                border-color: #73917c;
                box-shadow: 0 0 0 4px rgba(115, 145, 124, .12);
            }
            .input-wrap svg {
                width: 20px;
                height: 20px;
                flex: none;
            }
            .input-wrap input {
                height: 100%;
                width: 100%;
                border: 0;
                background: transparent;
                color: #18332d;
                outline: none;
                font-size: 14px;
            }
            .input-wrap input::placeholder { color: #a0aca5; }
            .remember-box {
                height: 17px;
                width: 17px;
                border-radius: 5px;
                border-color: #b9c8be;
                color: #173f35;
            }
            .login-button, .google-button {
                display: flex;
                height: 52px;
                width: 100%;
                align-items: center;
                justify-content: center;
                gap: 9px;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 800;
                transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            }
            .login-button {
                color: white;
                background: #173f35;
                box-shadow: 0 14px 24px rgba(23, 63, 53, .16);
            }
            .login-button svg { width: 18px; height: 18px; }
            .login-button:hover { transform: translateY(-2px); background: #215044; }
            .google-button {
                border: 1px solid #ccd9cf;
                color: #31584b;
                background: white;
            }
            .google-button svg { width: 20px; height: 20px; }
            .google-button:hover { background: #f8faf7; transform: translateY(-1px); }
            @media (max-width: 639px) {
                .login-card {
                    border-radius: 20px;
                    padding: 24px 20px;
                }
            }
            @media (prefers-reduced-motion: reduce) {
                .login-button, .google-button { transition: none; }
            }
        </style>
    </body>
</html>
