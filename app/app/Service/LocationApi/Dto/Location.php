<?php

namespace App\Service\LocationApi\Dto;

class Location
{
    public function __construct(
        public string $longitude,
        public string $latitude,
        public string $name,
    ){}
}
