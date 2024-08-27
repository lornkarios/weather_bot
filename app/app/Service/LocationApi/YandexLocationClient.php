<?php

namespace App\Service\LocationApi;

use App\Service\LocationApi\Dto\Location;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class YandexLocationClient implements LocationApiClient
{
    private string $token;
    private string $url;

    public function __construct(private Client $client)
    {
        $this->url = config('services.yandex_location_client.url');
        $this->token = config('services.yandex_location_client.token');
    }

    public function findLocation(string $address): ?Location
    {
        $response = $this->client->get($this->url . '/1.x/', [
            'query' => [
                'apikey' => $this->token,
                'geocode' => $address,
                'format' => 'json',
            ],
        ]);
        $jsonArr = json_decode((string)$response->getBody(), true);
        $results = Arr::get($jsonArr, 'response.GeoObjectCollection.featureMember', []);
        if (count($results) === 0) {
            return null;
        }
        $result = current($results);
        $name = Arr::get($result, 'GeoObject.metaDataProperty.GeocoderMetaData.text');
        $coordinates = Arr::get($result, 'GeoObject.Point.pos');
        if (is_null($name) || is_null($coordinates)) {
            return null;
        }
        $coordinatesArr = explode(' ', $coordinates);
        return new Location($coordinatesArr[0], $coordinatesArr[1], $name);
    }
}
