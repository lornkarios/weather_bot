<?php

namespace App\Service\Location;

use App\Models\Location;
use DefStudio\Telegraph\DTO\Location as TelegraphLocation;
use DefStudio\Telegraph\Models\TelegraphChat;

class LocationRepository
{
    public function forChatFirst(TelegraphChat $chat): ?Location
    {
        return Location::forChat($chat)->first();
    }

    public function saveLocation(TelegraphChat $chat, TelegraphLocation $location): void
    {
        Location::query()->upsert([
            'lon' => $location->longitude(),
            'lat' => $location->latitude(),
            'chat_id' => $chat->id,
        ], ['chat_id' => $chat->id]);
    }
}
