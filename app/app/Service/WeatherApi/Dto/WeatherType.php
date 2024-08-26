<?php

namespace App\Service\WeatherApi\Dto;

enum WeatherType:string
{
    case CLEAR_SKY = 'clear_sky';
    case PARTLY_CLOUDLY = 'partly_cloudly';
    case OVERCAST = 'overcast';
    case DRIZZLE = 'drizzle';
    case RAIN = 'rain';
    case SHOWFALL = 'snowfall';
    case FOG = 'fog';
    case STORM = 'storm';
}
