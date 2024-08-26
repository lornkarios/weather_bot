@php
/** @var \Illuminate\Support\Collection|\App\Service\WeatherApi\Dto\ManyDayWeather[] $weatherCollection */
@endphp
@foreach($weatherCollection as $part)
{{$part->date->translatedFormat('j F')}} @include('period', ['part'=> $part])
@endforeach
