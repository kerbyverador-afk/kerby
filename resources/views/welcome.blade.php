<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#173f35">

        <title>Boarding House Payment Status System</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans text-[#18332d] antialiased">
        <main class="landing-shell relative min-h-screen overflow-hidden bg-[#edf3e9]">
            <div class="decor decor-one" aria-hidden="true"></div>
            <div class="decor decor-two" aria-hidden="true"></div>

            <nav class="relative z-20 mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8 lg:px-10" aria-label="Main navigation">
                <a href="/" class="flex items-center gap-3" aria-label="Boarding House home">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none">
                            <path d="M8 21.5 24 9l16 12.5v17H8v-17Z" fill="currentColor"/>
                            <path d="M17 38V28h14v10M12 21.5V14h6v3" stroke="#173f35" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="24" cy="20" r="3" fill="#f4ca64"/>
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-extrabold tracking-[0.13em] text-[#173f35]">BHOUSE</span>
                        <span class="block text-[10px] font-bold uppercase tracking-[0.24em] text-[#73917c]">Payment System</span>
                    </span>
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="nav-button">
                        Go to dashboard
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @else
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="login-link">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="nav-button">
                                Register
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endauth
            </nav>

            <section class="relative z-10 mx-auto grid min-h-[calc(100vh-88px)] max-w-7xl items-center gap-10 px-5 pb-14 pt-8 sm:px-8 lg:grid-cols-[1.02fr_0.98fr] lg:px-10 lg:pb-20 lg:pt-4">
                <div class="max-w-2xl">
                    <div class="eyebrow">
                        <span class="eyebrow-dot"></span>
                        Simple, clear, and secure
                    </div>

                    <h1 class="mt-6 text-5xl font-extrabold leading-[0.98] tracking-[-0.055em] text-[#173f35] sm:text-6xl lg:text-[4.8rem]">
                        Boarding House
                        <span class="relative block text-[#dc9f32]">
                            Payment Status
                            <svg class="title-swoop" viewBox="0 0 420 24" fill="none" aria-hidden="true">
                                <path d="M3 17C100 2 268 3 417 14" stroke="#dc9f32" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        System
                    </h1>

                    <p class="mt-7 max-w-xl text-base leading-7 text-[#5f756c] sm:text-lg">
                        A simple way to track your monthly <strong class="font-bold text-[#31584b]">boarding-house payments</strong>. See what is paid, pending, or overdue.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="primary-button">
                                View my payments
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="primary-button">
                                Check payment status
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="none">
                                    <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="secondary-button">Create account</a>
                            @endif
                        @endauth
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-[#cbd8cd] pt-5 text-sm font-semibold text-[#587064]">
                        <span class="flex items-center gap-2">
                            <span class="feature-check">✓</span>
                            Fast updates
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="feature-check">✓</span>
                            Clear records
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="feature-check">✓</span>
                            Easy tracking
                        </span>
                    </div>
                    <a href="#terms-and-conditions" class="mt-5 inline-flex text-sm font-bold text-[#31584b] underline underline-offset-4">Read the Terms &amp; Conditions</a>
                </div>

                <div class="hero-art mx-auto w-full max-w-[590px] lg:max-w-none" aria-label="Boarding house payment status preview">
                    <div class="art-backdrop">
                        <div class="sun" aria-hidden="true"></div>
                        <div class="cloud cloud-one" aria-hidden="true"></div>
                        <div class="cloud cloud-two" aria-hidden="true"></div>

                        <svg class="boarding-house" viewBox="0 0 520 420" fill="none" role="img" aria-label="Friendly illustrated boarding house">
                            <ellipse cx="260" cy="379" rx="205" ry="20" fill="#9eb39f" fill-opacity=".45"/>
                            <path d="M75 378V205l181-122 181 122v173H75Z" fill="#fffaf0" stroke="#173f35" stroke-width="7" stroke-linejoin="round"/>
                            <path d="M48 210 256 70l208 140-24 20-184-124L72 230l-24-20Z" fill="#d9a746" stroke="#173f35" stroke-width="7" stroke-linejoin="round"/>
                            <path d="M359 121V72h48v157" fill="#fffaf0" stroke="#173f35" stroke-width="7" stroke-linejoin="round"/>
                            <path d="M349 72h68" stroke="#173f35" stroke-width="7" stroke-linecap="round"/>

                            <rect x="224" y="275" width="64" height="103" rx="32" fill="#e1b95d" stroke="#173f35" stroke-width="7"/>
                            <circle cx="273" cy="330" r="4" fill="#173f35"/>

                            <g stroke="#173f35" stroke-width="7" stroke-linejoin="round">
                                <rect x="112" y="247" width="74" height="62" rx="4" fill="#91bdad"/>
                                <path d="M149 247v62M112 278h74" stroke-width="5"/>
                                <rect x="326" y="247" width="74" height="62" rx="4" fill="#91bdad"/>
                                <path d="M363 247v62M326 278h74" stroke-width="5"/>
                            </g>

                            <path d="M100 378h320" stroke="#173f35" stroke-width="7" stroke-linecap="round"/>
                            <path d="M58 378c-24 0-43 19-43 43h86c0-24-19-43-43-43ZM462 378c-24 0-43 19-43 43h86c0-24-19-43-43-43Z" fill="#668f76" stroke="#173f35" stroke-width="7"/>

                            <g class="house-character">
                                <circle cx="101" cy="267" r="30" fill="#c77c50" stroke="#173f35" stroke-width="6"/>
                                <path d="M72 259c4-26 19-40 39-34 15 5 21 18 19 32-13-12-38-13-58 2Z" fill="#173f35"/>
                                <circle cx="91" cy="269" r="3" fill="#173f35"/>
                                <circle cx="112" cy="269" r="3" fill="#173f35"/>
                                <path d="M96 279c7 5 13 5 20 0" stroke="#173f35" stroke-width="4" stroke-linecap="round"/>
                                <path d="M72 310c7-14 20-21 39-21 18 0 31 7 38 21v68H72v-68Z" fill="#f2b94b" stroke="#173f35" stroke-width="6"/>
                            </g>
                        </svg>

                        <div class="status-card status-card-main">
                            <div class="status-icon paid-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="m7 12 3 3 7-7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <p class="status-label">Payment status</p>
                                <p class="status-value">PAID</p>
                            </div>
                        </div>

                        <div class="status-card status-card-mini">
                            <span class="mini-avatar" aria-hidden="true">JM</span>
                            <div>
                                <p class="mini-name">Juan M.</p>
                                <p class="mini-date">Nov. 21</p>
                            </div>
                            <span class="mini-paid">Paid</span>
                        </div>
                    </div>
                </div>
            </section>
            <section id="terms-and-conditions" class="relative z-10 mx-auto max-w-7xl px-5 pb-12 sm:px-8 lg:px-10">
                <div class="rounded-3xl border border-[#d3ded4] bg-white/90 p-5 shadow-sm sm:p-8">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#73917c]">Before you create an account</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#173f35]">Terms &amp; Conditions</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[#5f756c]">Please read these simple rules. You will need to agree to them when you create your account.</p>
                    <x-terms-and-conditions />
                    <a href="{{ route('register') }}#terms-agreement" class="primary-button mt-6">Create an account and agree to the terms</a>
                </div>
            </section>
        </main>

        <style>
            .landing-shell {
                background:
                    radial-gradient(circle at 13% 18%, rgba(255, 255, 255, .95) 0 5%, transparent 5.2%),
                    linear-gradient(135deg, #f4f7ef 0%, #edf3e9 58%, #e2eddd 100%);
            }
            .decor {
                position: absolute;
                border-radius: 9999px;
                pointer-events: none;
            }
            .decor-one {
                width: 260px;
                height: 260px;
                right: -130px;
                top: 70px;
                border: 42px solid rgba(217, 167, 70, .12);
            }
            .decor-two {
                width: 180px;
                height: 180px;
                left: -95px;
                bottom: -70px;
                background: rgba(102, 143, 118, .09);
            }
            .brand-mark {
                display: grid;
                height: 48px;
                width: 48px;
                place-items: center;
                border-radius: 15px;
                color: #f4ca64;
                background: #173f35;
                box-shadow: 0 10px 24px rgba(23, 63, 53, .16);
            }
            .brand-mark svg { width: 38px; height: 38px; }
            .login-link {
                border-radius: 12px;
                padding: 11px 16px;
                font-size: 14px;
                font-weight: 700;
                color: #31584b;
            }
            .login-link:hover { background: rgba(255, 255, 255, .65); }
            .nav-button, .primary-button, .secondary-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 9px;
                border-radius: 12px;
                font-weight: 800;
                transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            }
            .nav-button {
                padding: 11px 16px;
                color: white;
                background: #173f35;
                box-shadow: 0 8px 18px rgba(23, 63, 53, .14);
                font-size: 14px;
            }
            .nav-button svg, .primary-button svg { width: 18px; height: 18px; }
            .nav-button:hover, .primary-button:hover { transform: translateY(-2px); }
            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 9px;
                border: 1px solid #cbd8cd;
                border-radius: 9999px;
                background: rgba(255, 255, 255, .72);
                padding: 8px 12px;
                color: #4f6c5e;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .14em;
                text-transform: uppercase;
            }
            .eyebrow-dot {
                width: 8px;
                height: 8px;
                border-radius: 9999px;
                background: #dc9f32;
                box-shadow: 0 0 0 4px rgba(220, 159, 50, .14);
            }
            .title-swoop {
                position: absolute;
                left: 2%;
                bottom: -16px;
                width: 82%;
                height: 24px;
                opacity: .75;
            }
            .primary-button {
                min-height: 52px;
                padding: 14px 20px;
                color: white;
                background: #173f35;
                box-shadow: 0 14px 28px rgba(23, 63, 53, .18);
            }
            .secondary-button {
                min-height: 52px;
                padding: 14px 18px;
                border: 1px solid #b9cbbd;
                color: #31584b;
                background: rgba(255, 255, 255, .62);
            }
            .secondary-button:hover { background: white; transform: translateY(-2px); }
            .feature-check {
                display: grid;
                height: 20px;
                width: 20px;
                place-items: center;
                border-radius: 9999px;
                color: white;
                background: #73917c;
                font-size: 11px;
            }
            .art-backdrop {
                position: relative;
                min-height: 520px;
                border: 1px solid rgba(122, 151, 128, .25);
                border-radius: 42px 42px 180px 42px;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, .72), rgba(230, 239, 224, .75)),
                    #dce9d5;
                box-shadow: 0 28px 70px rgba(54, 91, 70, .16);
                isolation: isolate;
            }
            .art-backdrop::after {
                content: "";
                position: absolute;
                right: 25px;
                bottom: 22px;
                width: 75%;
                height: 80%;
                border: 2px solid rgba(255, 255, 255, .55);
                border-radius: 38px 38px 150px 38px;
                pointer-events: none;
            }
            .sun {
                position: absolute;
                top: 42px;
                right: 62px;
                width: 70px;
                height: 70px;
                border-radius: 9999px;
                background: #efc86a;
                box-shadow: 0 0 0 14px rgba(239, 200, 106, .15);
            }
            .cloud {
                position: absolute;
                height: 18px;
                border-radius: 9999px;
                background: rgba(255, 255, 255, .78);
            }
            .cloud::before, .cloud::after {
                content: "";
                position: absolute;
                bottom: 4px;
                border-radius: 9999px;
                background: inherit;
            }
            .cloud::before { left: 14px; width: 28px; height: 28px; }
            .cloud::after { left: 38px; width: 20px; height: 20px; }
            .cloud-one { left: 50px; top: 72px; width: 82px; }
            .cloud-two { right: 155px; top: 125px; width: 58px; opacity: .7; }
            .boarding-house {
                position: absolute;
                z-index: 2;
                left: 8%;
                bottom: 18px;
                width: 84%;
                height: 82%;
            }
            .house-character { transform-origin: center bottom; animation: wave 3.4s ease-in-out infinite; }
            .status-card {
                position: absolute;
                z-index: 5;
                display: flex;
                align-items: center;
                background: rgba(255, 255, 255, .96);
                box-shadow: 0 16px 36px rgba(31, 69, 53, .17);
                backdrop-filter: blur(8px);
            }
            .status-card-main {
                top: 56px;
                left: -28px;
                gap: 12px;
                width: 190px;
                border: 1px solid rgba(101, 143, 118, .18);
                border-radius: 18px;
                padding: 12px;
            }
            .status-icon {
                display: grid;
                height: 46px;
                width: 46px;
                flex: none;
                place-items: center;
                border-radius: 14px;
            }
            .status-icon svg { width: 25px; height: 25px; }
            .paid-icon { color: #32795b; background: #dceee3; }
            .status-label { color: #7b8f84; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
            .status-value { color: #32795b; font-size: 18px; line-height: 1.2; font-weight: 900; letter-spacing: .05em; }
            .status-card-mini {
                right: -18px;
                bottom: 58px;
                gap: 9px;
                min-width: 210px;
                border: 1px solid rgba(101, 143, 118, .16);
                border-radius: 16px;
                padding: 10px;
            }
            .mini-avatar {
                display: grid;
                height: 38px;
                width: 38px;
                place-items: center;
                border-radius: 50%;
                color: white;
                background: #6a947c;
                font-size: 11px;
                font-weight: 800;
            }
            .mini-name { color: #31584b; font-size: 12px; line-height: 1.2; font-weight: 800; }
            .mini-date { color: #91a097; font-size: 10px; }
            .mini-paid {
                margin-left: auto;
                border-radius: 9999px;
                background: #e2efe6;
                padding: 5px 8px;
                color: #32795b;
                font-size: 9px;
                font-weight: 900;
                text-transform: uppercase;
            }
            @keyframes wave {
                0%, 100% { transform: rotate(0deg); }
                50% { transform: rotate(-2deg) translateY(-2px); }
            }
            @media (prefers-reduced-motion: reduce) {
                .house-character { animation: none; }
                .nav-button, .primary-button, .secondary-button { transition: none; }
            }
            @media (max-width: 1023px) {
                .art-backdrop { min-height: 480px; }
            }
            @media (max-width: 640px) {
                .art-backdrop { min-height: 380px; border-radius: 28px 28px 120px 28px; }
                .status-card-main { left: 8px; top: 28px; width: 170px; }
                .status-card-mini { right: 8px; bottom: 28px; min-width: 190px; }
            }
        </style>
    </body>
</html>
