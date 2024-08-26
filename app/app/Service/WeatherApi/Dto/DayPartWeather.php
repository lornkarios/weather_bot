<?php

namespace App\Service\WeatherApi\Dto;

class DayPartWeather
{
    public function __construct(
        public WeatherType $type,
        public int $temperatureMin,
        public int $temperatureMax,
        public int $precipitationMm,
        public int $windSpeedMs,
    ) {
    }
}
