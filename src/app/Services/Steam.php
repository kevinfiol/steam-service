<?php declare(strict_types = 1);

namespace App\Services;

use App\Config\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class Steam
{
    const API_URL   = 'http://api.steampowered.com/';
    const STORE_URL = 'http://store.steampowered.com/api/';

    private $apiKey;
    private $client;

    public function __construct(Config $config, Client $client)
    {
        $appConfig = $config->get('app');
        $this->apiKey = $appConfig['steam_api'];
        $this->client = $client;
    }

    public function apiCall(string $iface, string $command, string $version, array $params = []): string
    {
        $params['key'] = $this->apiKey;
        $uri = self::API_URL . "{$iface}/{$command}/{$version}/";

        try {
            $res = $this->client->get($uri, ['query' => $params]);
        } catch(\Exception $e) {
            throw new \Exception('WEB API ERROR', $e->getCode());
        }

        return $res->getBody()->getContents();
    }

    public function apiAsync(string $iface, string $command, string $version, array $params = []): PromiseInterface
    {
        $params['key'] = $this->apiKey;
        $uri = self::API_URL . "{$iface}/{$command}/{$version}/";
        return $this->client->getAsync($uri, ['query' => $params]);
    }

    public function storeCall(string $command, array $params = []): string
    {
        $uri = self::STORE_URL . "{$command}/";

        try {
            $res = $this->client->get($uri, ['query' => $params]);
        } catch(\Exception $e) {
            throw new \Exception('STORE API ERROR', $e->getCode());
        }

        return $res->getBody()->getContents();
    }
}