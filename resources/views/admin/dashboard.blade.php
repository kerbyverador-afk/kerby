<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Kerby / Administration</p>
                <h2 class="mt-1 text-xl font-semibold leading-tight text-gray-900">Welcome, Admin!</h2>
            </div>
            <span class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-emerald-900">
                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                Local weather
            </span>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-7rem)] bg-[#f2f5f2] py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-800">Weather overview</p>
                    <h1 class="mt-1 text-2xl font-semibold text-gray-900">Conditions near you</h1>
                </div>
                <p class="text-sm text-gray-600">Current report <span class="px-1 text-gray-400">/</span> Open-Meteo</p>
            </div>

            <section class="overflow-hidden rounded-lg border border-[#d5dfd8] bg-white shadow-sm" aria-labelledby="weather-heading">
                <div class="grid md:grid-cols-[1.1fr_0.9fr]">
                    <div class="flex min-h-[330px] flex-col justify-between bg-[#183d38] p-6 text-white sm:p-9">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#c6d9b7]">Weather station</p>
                                <h2 id="weather-heading" class="mt-3 max-w-lg break-words text-2xl font-semibold leading-snug sm:text-3xl">
                                    {{ $weather['location'] ?? config('services.weather.location') }}
                                </h2>
                            </div>
                            <svg class="mt-1 h-14 w-14 shrink-0 text-[#edc66f] sm:h-16 sm:w-16" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                <circle cx="42" cy="20" r="10" stroke="currentColor" stroke-width="2.5" />
                                <path d="M42 3v5M42 32v5M25 20h5M54 20h5M30 8l4 4M50 28l4 4M54 8l-4 4" stroke="currentColor" stroke-linecap="round" stroke-width="2.5" />
                                <path d="M14 45.5a10.5 10.5 0 0 1 10.5-10.5c1 0 2 .14 2.9.4A15 15 0 0 1 56 41c4.2.3 7.5 3.8 7.5 8.1A8 8 0 0 1 55.5 57h-37A9.5 9.5 0 0 1 14 45.5Z" fill="#183d38" stroke="#e8f0e5" stroke-linejoin="round" stroke-width="2.5" />
                            </svg>
                        </div>

                        @if ($weather['available'])
                            <div class="mt-10">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#c6d9b7]">Current temperature</p>
                                <div class="mt-2 flex flex-wrap items-start gap-x-3 gap-y-1">
                                    <p class="text-6xl font-semibold leading-none tabular-nums sm:text-7xl">{{ $weather['temperature'] }}<span class="align-top text-3xl font-medium text-[#edc66f]">&deg;</span></p>
                                    <p class="pt-2 text-sm font-medium text-[#e4ece3]">Celsius</p>
                                </div>
                            </div>
                        @else
                            <div class="mt-10 border-l-2 border-[#edc66f] pl-4" role="status">
                                <p class="text-sm font-semibold text-white">Weather report unavailable</p>
                                <p class="mt-1 max-w-md text-sm leading-relaxed text-[#e4ece3]">
                                    {{ $weather['message'] ?? 'Weather information is temporarily unavailable. Please try again later.' }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col justify-between bg-[#f6f7f1] p-6 sm:p-9">
                        @if ($weather['available'])
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Conditions</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183d38] sm:text-3xl">{{ $weather['condition'] }}</p>
                            </div>

                            <div class="mt-10 grid grid-cols-2">
                                <div class="border-b border-r border-[#d5dfd8] pb-5 pr-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Humidity</p>
                                    <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-900">{{ $weather['humidity'] }}<span class="ml-0.5 text-lg font-medium text-emerald-800">%</span></p>
                                    <p class="mt-1 text-sm text-gray-600">Relative humidity</p>
                                </div>
                                <div class="border-b border-[#d5dfd8] pb-5 pl-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Wind speed</p>
                                    <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-900">{{ $weather['wind_speed'] }}</p>
                                    <p class="mt-1 text-sm text-gray-600">Kilometres per hour</p>
                                </div>
                                <div class="col-span-2 flex items-center justify-between gap-4 pt-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Data provider</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-800">Open-Meteo</p>
                                    </div>
                                    <span class="inline-flex items-center gap-2 rounded-md bg-[#e5eddf] px-3 py-2 text-xs font-semibold text-[#315746]">
                                        <span class="h-2 w-2 rounded-full bg-[#6e9a5a]"></span>
                                        Current report
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="flex min-h-48 flex-col justify-center border-t border-[#d5dfd8] pt-6 md:border-l md:border-t-0 md:pl-8 md:pt-0">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Readings</p>
                                <p class="mt-3 text-lg font-semibold text-gray-800">No current measurements</p>
                                <p class="mt-1 text-sm leading-relaxed text-gray-600">Temperature, condition, humidity, and wind readings will appear here when the service responds.</p>
                            </div>
                            <p class="mt-8 border-t border-[#d5dfd8] pt-4 text-sm text-gray-600">Data provider <span class="font-semibold text-gray-800">Open-Meteo</span></p>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>