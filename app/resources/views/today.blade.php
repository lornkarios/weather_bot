@php
/** @var \App\Service\WeatherApi\Dto\OneDayWeather $weather */
@endphp
{{$weather->date->translatedFormat('j F')}}

@foreach(['night','morning','day','evening'] as $partCode)
@php
$part = $weather->$partCode;
$labelPart = __('telegram_bot.'.$partCode);
@endphp
{{$labelPart}}  @include('period', ['part'=> $part])
@endforeach
