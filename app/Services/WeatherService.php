<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function current(): array
    {
        $client = Http::acceptJson()
            ->connectTimeout(config('services.weather.connect_timeout'))
            ->timeout(config('services.weather.timeout'));

        try {
            $forecastResponse = $client->get(config('services.weather.forecast_url'), [
                'latitude' => config('services.weather.latitude'),
                'longitude' => config('services.weather.longitude'),
                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                'temperature_unit' => 'celsius',
                'wind_speed_unit' => 'kmh',
                'timezone' => 'auto',
            ]);

            if (! $forecastResponse->successful()) {
                return $this->unavailable();
            }

            $current = $forecastResponse->json('current');
            if (! is_array($current) || ! $this->hasWeatherValues($current)) {
                return $this->unavailable();
            }

            return [
                'available' => true,
                'location' => config('services.weather.location'),
                'temperature' => $current['temperature_2m'],
                'condition' => $this->description((int) $current['weather_code']),
                'humidity' => $current['relative_humidity_2m'],
                'wind_speed' => $current['wind_speed_10m'],
            ];
        } catch (ConnectionException) {
            return $this->unavailable();
        }
    }

    private function hasWeatherValues(array $current): bool
    {
        foreach (['temperature_2m', 'relative_humidity_2m', 'weather_code', 'wind_speed_10m'] as $key) {
            if (! isset($current[$key]) || ! is_numeric($current[$key])) {
                return false;
            }
        }

        return true;
    }

    private function description(int $code): string
    {
        return match ($code) {
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45, 48 => 'Fog',
            51, 53, 55 => 'Drizzle',
            56, 57 => 'Freezing drizzle',
            61, 63, 65 => 'Rain',
            66, 67 => 'Freezing rain',
            71, 73, 75, 77 => 'Snow',
            80, 81, 82 => 'Rain showers',
            85, 86 => 'Snow showers',
            95, 96, 99 => 'Thunderstorm',
            default => 'Conditions unavailable',
        };
    }

    private function unavailable(): array
    {
        return [
            'available' => false,
            'message' => 'Weather information is temporarily unavailable. Please try again later.',
        ];
    }
}
