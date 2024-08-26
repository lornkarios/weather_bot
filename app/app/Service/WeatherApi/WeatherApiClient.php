<?php

namespace App\Service\WeatherApi;

use App\Models\Location;
use App\Service\WeatherApi\Dto\ManyDayWeather;
use App\Service\WeatherApi\Dto\OneDayWeather;
use Illuminate\Support\Collection;

interface WeatherApiClient
{
    public function today(Location $location): OneDayWeather;

    /**
     * @return Collection|OneDayWeather[]
     */
    public function for3d(Location $location): Collection;

    /**
     * @return Collection|ManyDayWeather
     */
    public function forWeek(Location $location):Collection;
}
