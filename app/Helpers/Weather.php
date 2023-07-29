<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Http;

class Weather
{
    public $client;
    public $city;
    public $locationApiUrl;
    public $watherApiUrl;
    public $yandexWeatherUrl;


    public function __construct($city)
    {
        $this->client = new Client();
        $this->locationApiUrl = config('weather-api.location_api') . '?key=' . config('weather-api.key');
        $this->watherApiUrl = config('weather-api.api') . '?key=' . config('weather-api.key');
        $this->yandexWeatherUrl = config('yandex-weather.url');
        $this->city = $city;
    }

    public function checkLocation()
    {
        $locationData = $this->getLocationData();
        $data = [
            'success' => true
        ];
        if ($locationData['success'] && empty($locationData['data'])) {
            $data['success'] = false;
            $data['message'] = 'Location not found';
            $data['status'] = 404;
        } elseif (!$locationData['success']) {
            $data['success'] = false;
            $data['message'] = $locationData['message'];
            $data['status'] = $locationData['status'];
        } else {
            $data['location'] = $locationData['data'][0];
        }
        return $data;
    }

    public function getLocationData()
    {
        $url = $this->locationApiUrl . "&q={$this->city}&aqi=no";
        return $this->handleRequest('get', $url);
    }


    public function weatherApiData($city)
    {
        $url = $this->watherApiUrl . "&q={$city}&aqi=no";
        $result = $this->handleRequest('get', $url);
        $data = [];
        if(!$result['success'] || empty($result['data'])) {
            $data['success'] = false;
            $data['message'] = $result['message'] ?? 'Empty data';
        } else {
            $data['success'] = true;
            $data['temp'] = $result['data']['current']['temp_c'];
        }
        return $data;
    }

    public function yandexWeatherData($lat, $lon)
    {
        $params = [
            'headers' => [
                'X-Yandex-API-Key' => config('yandex-weather.key')
            ]
        ];

        $data = [];
        $url = $this->yandexWeatherUrl . "?lat={$lat}&lon={$lon}";
        $result = $this->handleRequest('get', $url, $params);
        if(!$result['success'] || empty($result['data'])) {
            $data['success'] = false;
            $data['message'] = $result['message'] ?? 'Empty data';
        } else {
            $data['success'] = true;
            $data['temp'] = $result['data']['fact']['temp'];
        }
        return $data;
    }

    public function handleRequest($method, $url, $params = [])
    {
        try {
            $response = $this->client->request($method, $url, $params);
            $data = json_decode($response->getBody(), true);
            return ['success' => true, 'data' => $data];
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if($response->getStatusCode() === 403) {
                return ['success' => false, 'message' => $response->getReasonPhrase(), 'status' => 403];
            }

            $responseBodyAsString = $response->getBody()->getContents();
            $errorResponse = json_decode($responseBodyAsString, true);
            return ['success' => false, 'message' => $errorResponse['error']['message'], 'status', 400];
        }
    }
}
