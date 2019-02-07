<?php declare(strict_types = 1);

namespace App\Services;

use GuzzleHttp\Client;

class Steam
{
    private $apiKey;
    private $client;
    
    const API_URL   = 'http://api.steampowered.com/';
    const STORE_URL = 'http://store.steampowered.com/api/';

    public function __construct(string $apiKey, Client $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    public function apiCall(string $iface, string $command, string $version, array $params = [])
    {
        $uri = self::API_URL . "{$iface}/{$command}/{$version}/";
        $params['key'] = $this->apiKey;

        $res = $this->client->get($uri, ['query' => $params]);
        return $res->getBody()->getContents();
    }

    public function storeCall(string $command, array $params = [])
    {
        $uri = self::STORE_URL . "{$command}/";
        $res = $this->client->get($uri, ['query' => $params]);
        return $res->getBody()->getContents();
    }
}