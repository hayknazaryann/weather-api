<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Weather;
use App\Helpers\WeatherApi;
use App\Helpers\YandexWeather;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     *  @OA\Get(
     *      path="/api/weather?city=",
     *      summary="Get temperature from sources and their average",
     *      tags={"weather"},
     *      @OA\Parameter(
     *         name="city",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *              type="string",
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="The city not found"
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Somethinng went wrong"
     *      ),
     *  )
     */
    public function getByCity()
    {
        if (!empty(\request()->get('city'))) {
            $weatherInit = new Weather(\request()->get('city'));
            $check = $weatherInit->checkLocation();
            if (!$check['success']) {
                return response()->json(
                    ['success' => false, ['message' => $check['message']]],
                    $check['status']
                );
            }
            $location = $check['location'];
            $weatherApiData = $weatherInit->weatherApiData($location['name']);
            $yandexWeatherData = $weatherInit->yandexWeatherData($location['lat'], $location['lon']);

            $responseData = [];
            if($weatherApiData['success'] && $yandexWeatherData['success']) {
                $responseData['success'] = true;
                $responseData['data']['average'] = ($weatherApiData['temp'] + $yandexWeatherData['temp'])/2;
                $responseData['data']['yandex'] = $yandexWeatherData['temp'];
                $responseData['data']['weather_api'] = $weatherApiData['temp'];
            }

            return response()->json($responseData, 201);
        }
        return response()->json(
            ['success' => false, ['message' => 'Parameter city required']],
            400
        );


    }
}
