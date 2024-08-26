@php
/** @var \Illuminate\Support\Collection $weatherCollection */
@endphp
@foreach($weatherCollection as $weather)
@include('today',['weather' => $weather])


@endforeach
