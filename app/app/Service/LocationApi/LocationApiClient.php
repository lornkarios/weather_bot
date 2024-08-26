<?php

namespace App\Service\LocationApi;

use App\Service\LocationApi\Dto\Location;

interface LocationApiClient
{
    public function findLocation(string $address): ?Location;
}
