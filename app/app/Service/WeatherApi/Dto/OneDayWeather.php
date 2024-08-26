<?php

namespace App\Service\WeatherApi\Dto;

use Carbon\Carbon;

class OneDayWeather
{
    public function __construct(
        public Carbon $date,
        public DayPartWeather $night,
        public DayPartWeather $morning,
        public DayPartWeather $day,
        public DayPartWeather $evening,
    ) {
    }
}
