@php use App\Service\WeatherApi\Dto\WeatherType; @endphp
@php
    /** @var \App\Service\WeatherApi\Dto\DayPartWeather|\App\Service\WeatherApi\Dto\ManyDayWeather $part */
@endphp
{{$part->temperatureMin}}°- {{$part->temperatureMax}}° {{
    match ($part->type){
        WeatherType::CLEAR_SKY => '☀',
        WeatherType::PARTLY_CLOUDLY => '🌤',
        WeatherType::OVERCAST => '⛅',
        WeatherType::DRIZZLE => '🌦',
        WeatherType::FOG => '🌫',
        WeatherType::RAIN => '🌧',
        WeatherType::SHOWFALL => '❄',
        WeatherType::STORM => '⛈',
    }
}} @if($part->precipitationMm){{$part->precipitationMm}} мм. @endif 💨 {{$part->windSpeedMs}} м/с
