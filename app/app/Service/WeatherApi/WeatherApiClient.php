<?php

namespace App\Service\WeatherApi;

use App\Models\Location;
use App\Service\WeatherApi\Dto\ManyDayWeather;
use App\Service\WeatherApi\Dto\OneDayWeather;
use Illuminate\Support\Collection;

interface WeatherApiClient
{
    /**
     * @return Collection|OneDayWeather[]
     */
    public function oneDayFormat(Location $location, int $days = 1): Collection;

    /**
     * @return Collection|ManyDayWeather
     */
    public function manyDayFormat(Location $location, int $days = 7):Collection;
}
