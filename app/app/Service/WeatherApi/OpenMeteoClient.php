<?php

namespace App\Service\WeatherApi;

use App\Models\Location;
use App\Service\WeatherApi\Dto\DayPartWeather;
use App\Service\WeatherApi\Dto\ManyDayWeather;
use App\Service\WeatherApi\Dto\OneDayWeather;
use App\Service\WeatherApi\Dto\WeatherType;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OpenMeteoClient implements WeatherApiClient
{
    private string $url;

    public function __construct(private Client $client)
    {
        $this->url = config('services.open_meteo.url');
    }

    public function today(Location $location): OneDayWeather
    {
        $response = $this->client->get(
            $this->url . '/v1/forecast',
            [
                'query' => [
                    'latitude' => $location->lat,
                    'longitude' => $location->lon,
                    'hourly' => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                    'wind_speed_unit' => 'ms',
                    'timezone' => 'auto',
                    'forecast_days' => 1,
                ]
            ]
        );
        $json = (string)$response->getBody();
        $jsonArr = json_decode($json, true);
        return $this->oneDayWeatherCollection($jsonArr)->first();
    }

    public function for3d(Location $location): Collection
    {
        $response = $this->client->get(
            $this->url . '/v1/forecast',
            [
                'query' => [
                    'latitude' => $location->lat,
                    'longitude' => $location->lon,
                    'hourly' => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                    'wind_speed_unit' => 'ms',
                    'timezone' => 'auto',
                    'forecast_days' => 3,
                ]
            ]
        );
        $json = (string)$response->getBody();
        $jsonArr = json_decode($json, true);
        return $this->oneDayWeatherCollection($jsonArr);
    }

    public function oneDayWeatherCollection(array $responseArr): Collection
    {
        $weatherCollection = collect([]);
        $curDateArr = null;
        foreach ($responseArr['hourly']['time'] as $key => $dateTime) {
            $date = Carbon::make($dateTime, $responseArr['timezone']);
            if (is_null($curDateArr)) {
                $curDateArr = $this->initCurDate($date);
            }
            if ($date->diff($curDateArr['date'])->d !== 0) {
                $weatherCollection[] = $this->weatherItemFromArr($curDateArr);
                $curDateArr = $this->initCurDate($date);
            }
            $periodCode = match (true) {
                in_array($date->hour, [0, 1, 2, 3], true) => 'night',
                in_array($date->hour, [4, 5, 6, 7, 8, 9], true) => 'morning',
                in_array($date->hour, [10, 11, 12, 13, 14, 15, 16], true) => 'day',
                in_array($date->hour, [17, 18, 19, 20, 21, 22, 23], true) => 'evening',
            };
            $curDateArr[$periodCode]['t'][] = $responseArr['hourly']['temperature_2m'][$key];
            $curDateArr[$periodCode]['p'][] = $responseArr['hourly']['precipitation'][$key];
            $curDateArr[$periodCode]['ws'][] = $responseArr['hourly']['wind_speed_10m'][$key];
            $curDateArr[$periodCode]['wc'][] = $responseArr['hourly']['weather_code'][$key];
        }
        $weatherCollection[] = $this->weatherItemFromArr($curDateArr);
        return $weatherCollection;
    }

    private function initCurDate(Carbon $date): array
    {
        return [
            'date' => $date,
            'night' => ['t' => [], 'p' => [], 'ws' => [], 'wc' => []],
            'morning' => ['t' => [], 'p' => [], 'ws' => [], 'wc' => []],
            'day' => ['t' => [], 'p' => [], 'ws' => [], 'wc' => []],
            'evening' => ['t' => [], 'p' => [], 'ws' => [], 'wc' => []],
        ];
    }

    private function weatherItemFromArr(array $curDateArr): OneDayWeather
    {
        return new OneDayWeather(

            $curDateArr['date'],
            ...array_map(function ($periodCode) use ($curDateArr) {
                $countWeatherCode = array_count_values($curDateArr[$periodCode]['wc']);
                arsort($countWeatherCode);

                return new DayPartWeather(
                    $this->weatherTypeFromCode(key($countWeatherCode)),
                    min($curDateArr[$periodCode]['t']),
                    max($curDateArr[$periodCode]['t']),
                    array_sum($curDateArr[$periodCode]['p']),
                    (int)(array_sum($curDateArr[$periodCode]['ws']) / count($curDateArr[$periodCode]['ws'])),
                );
            }, ['night', 'morning', 'day', 'evening'])
        );
    }

    public function forWeek(Location $location): Collection
    {
        $response = $this->client->get(
            $this->url . '/v1/forecast',
            [
                'query' => [
                    'latitude' => $location->lat,
                    'longitude' => $location->lon,
                    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,weather_code',
                    'wind_speed_unit' => 'ms',
                    'timezone' => 'auto',
                ]
            ]
        );

        $json = (string)$response->getBody();
        $jsonArr = json_decode($json, true);
        $weatherCollection = collect([]);
        foreach ($jsonArr['daily']['time'] as $key => $date) {
            $weatherCollection->add(
                new ManyDayWeather(
                    Carbon::make($date, $jsonArr['timezone']),
                    $this->weatherTypeFromCode($jsonArr['daily']['weather_code'][$key]),
                    $jsonArr['daily']['temperature_2m_min'][$key],
                    $jsonArr['daily']['temperature_2m_max'][$key],
                    $jsonArr['daily']['precipitation_sum'][$key],
                    $jsonArr['daily']['wind_speed_10m_max'][$key],
                )
            );
        }
        return $weatherCollection;
    }


    /**
     *
     * 0 Ясное небо
     * 1, 2, 3 В основном ясно, переменная облачность и сплошная облачность
     * 45, 48 Туман и оседающий изморозь
     * 51, 53, 55 Морось: слабая, умеренная и плотная интенсивность
     * 56, 57 Замерзающая морось: слабая и плотная интенсивность
     * 61, 63, 65 Дождь: слабая, умеренная и сильная интенсивность
     * 66, 67 Замерзающий дождь: слабая и сильная интенсивность
     * 71, 73, 75 Снегопад: слабая, умеренная и сильная интенсивность
     * 77 Снежные зерна
     * 80, 81, 82 Ливневые дожди: слабые, умеренные и сильные
     * 85, 86 Снежные ливни слабые и сильные
     * 95 * Гроза: слабая или умеренная
     * 96, 99 * Гроза с слабым и сильным градом
     */
    private function weatherTypeFromCode(int $code): WeatherType
    {
        return match ($code) {
            0, 1 => WeatherType::CLEAR_SKY,
            2 => WeatherType::PARTLY_CLOUDLY,
            3 => WeatherType::OVERCAST,
            45, 48 => WeatherType::FOG,
            51, 53, 55, 56, 57 => WeatherType::DRIZZLE,
            61, 63, 65, 66, 67, 80, 81, 82, 85, 86 => WeatherType::RAIN,
            71, 73, 75, 77 => WeatherType::SHOWFALL,
            95, 96, 99 => WeatherType::STORM,
            default => WeatherType::CLEAR_SKY,
        };
    }
}
