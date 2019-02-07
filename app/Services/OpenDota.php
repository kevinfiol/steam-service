<?php declare(strict_types = 1);

namespace App\Services;

use GuzzleHttp\Client;

class OpenDota
{
    const API_URL = 'https://api.opendota.com/api/';
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function apiCall(string $interface, string $identifier = null, string $option = null): string
    {
        $uri = self::API_URL . "{$interface}/";
        if ($identifier) $uri = $uri . "{$identifier}/";
        if ($option)     $uri = $uri . "{$option}/";

        $res = $this->client->get($uri);
        return $res->getBody()->getContents();
    }
}