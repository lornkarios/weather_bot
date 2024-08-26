<?php

namespace App\Service\Location;

use App\Models\Location;
use App\Service\LocationApi\Dto\Location as LocationDto;
use DefStudio\Telegraph\Models\TelegraphChat;

class LocationRepository
{
    public function forChatFirst(TelegraphChat $chat): ?Location
    {
        return Location::forChat($chat)->first();
    }

    public function saveLocation(TelegraphChat $chat, LocationDto $location): Location
    {
       return Location::query()->updateOrCreate([
            'lon' => $location->longitude,
            'lat' => $location->latitude,
            'name' => $location->name,
            'chat_id' => $chat->id,
        ], ['chat_id' => $chat->id]);
    }
}
