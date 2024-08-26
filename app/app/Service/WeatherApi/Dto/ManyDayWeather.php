<?php

namespace App\Service\WeatherApi\Dto;

use Illuminate\Support\Carbon;

class ManyDayWeather
{
    public function __construct(
        public Carbon $date,
        public WeatherType $type,
        public int $temperatureMin,
        public int $temperatureMax,
        public int $precipitationMm,
        public int $windSpeedMs,
    ) {
    }
}
