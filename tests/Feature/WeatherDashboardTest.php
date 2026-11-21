<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherDashboardTest extends TestCase
{
    public function test_admin_dashboard_displays_mapped_weather_from_open_meteo(): void
    {
        config([
            'services.weather.location' => 'Arakan, North Cotabato, Philippines',
            'services.weather.latitude' => 7.352162,
            'services.weather.longitude' => 125.1282103,
        ]);
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 29.4,
                    'relative_humidity_2m' => 72,
                    'weather_code' => 2,
                    'wind_speed_10m' => 8.1,
                ],
            ]),
        ]);

        $this->actingAs(new User(['name' => 'Admin', 'email' => 'admin@example.test', 'role' => 'admin']))
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Welcome, Admin!')
            ->assertSee('Arakan, North Cotabato, Philippines')
            ->assertSee('29.4')
            ->assertSee('Partly cloudy')
            ->assertSee('72')
            ->assertSee('Relative humidity')
            ->assertSee('8.1')
            ->assertSee('Kilometres per hour');

        Http::assertSentCount(1);
        Http::assertSent(function ($request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_contains($request->url(), 'api.open-meteo.com')
                && $query['latitude'] === '7.352162'
                && $query['longitude'] === '125.1282103'
                && $query['current'] === 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m';
        });
    }

    public function test_admin_dashboard_shows_fallback_when_weather_api_returns_an_error(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['error' => true], 503),
        ]);

        $this->actingAs(new User(['name' => 'Admin', 'email' => 'admin@example.test', 'role' => 'admin']))
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Weather information is temporarily unavailable. Please try again later.');
    }

    public function test_admin_dashboard_shows_fallback_when_current_weather_data_is_missing(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['current' => []]),
        ]);

        $this->actingAs(new User(['name' => 'Admin', 'email' => 'admin@example.test', 'role' => 'admin']))
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Weather information is temporarily unavailable. Please try again later.');
    }

    public function test_admin_dashboard_shows_fallback_when_weather_api_connection_fails(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection timed out.'));

        $this->actingAs(new User(['name' => 'Admin', 'email' => 'admin@example.test', 'role' => 'admin']))
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Weather information is temporarily unavailable. Please try again later.');
    }
}
