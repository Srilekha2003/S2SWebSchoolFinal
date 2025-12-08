<?php

namespace App\Libraries;

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

class WeatherService
{
    protected string $apiKey;
    protected $client;

    public function __construct()
    {
        $this->apiKey = env('OW_API_KEY') ?: getenv('OW_API_KEY');
        $this->client = Services::curlrequest([
            'timeout' => 10,
            'verify'  => true,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'ManaVillage-Weather/1.0'
            ]
        ]);
    }

    protected function callAPI(string $url): ?array
    {
        try {
            $res = $this->client->get($url);
            if ($res->getStatusCode() !== 200) {
                log_message('warning', "[WeatherService] non-200 response {$res->getStatusCode()} for {$url}");
                return json_decode($res->getBody(), true);
            }
            return json_decode($res->getBody(), true);
        } catch (\Throwable $e) {
            log_message('error', '[WeatherService] API request failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Current weather by coordinates
     */
    public function getWeatherByCoordinates($lat, $lon): ?array
    {
        if (empty($lat) || empty($lon)) return null;
        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$this->apiKey}&units=metric";
        return $this->callAPI($url);
    }

    /**
     * Current weather by city name
     */
    public function getWeatherByCity(string $city): ?array
    {
        if (empty($city)) return null;
        $city = urlencode($city);
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$this->apiKey}&units=metric";
        return $this->callAPI($url);
    }

    /**
     * Forecast (One Call) - up to 7 days including alerts
     * Note: OpenWeather has different endpoints / versions — using One Call v3.0 or v2.5 depending on your subscription.
     * This uses the v2.5 onecall for compatibility. You can switch to v3.0 if preferred.
     */
    public function getForecast($lat, $lon): ?array
    {
        if (empty($lat) || empty($lon)) return null;

        // exclude hourly/minutely for smaller payload
        $url = "https://api.openweathermap.org/data/2.5/onecall?lat={$lat}&lon={$lon}&exclude=minutely,hourly&units=metric&appid={$this->apiKey}";
        return $this->callAPI($url);
    }
}
