<?php declare(strict_types = 1);

use GuzzleHttp\Client;

use App\Controllers\AppController;
use App\Controllers\SteamController;
use App\Controllers\OpenDotaController;

use App\Services\Steam;
use App\Services\OpenDota;

return function (array $config) {
    return [
        'heroDict' => function () use ($config) {
            $heroDict = file_get_contents($config['HERO_PATH']);
            return json_decode($heroDict, true);
        },

        'GuzzleHttp\Client' => function () {
            return new Client();
        },

        'App\Services\Steam' => function ($c) use ($config) {
            $client = $c->get('GuzzleHttp\Client');
            return new Steam($config['STEAM_API'], $client);
        },

        'App\Services\OpenDota' => function ($c) {
            $client = $c->get('GuzzleHttp\Client');
            return new OpenDota($client);
        },

        'App\Controllers\AppController' => function ($c) {
            $dota   = $c->get('App\Services\OpenDota');
            $steam  = $c->get('App\Services\Steam');
            $heroDict = $c->get('heroDict');
            return new AppController($steam, $dota, $heroDict);
        },

        'App\Controllers\SteamController' => function ($c) {
            $steam = $c->get('App\Services\Steam');
            return new SteamController($steam);
        },

        'App\Controllers\OpenDotaController' => function ($c) {
            $dota = $c->get('App\Services\OpenDota');
            return new OpenDotaController($dota);
        },

        'notFoundHandler' => function () {
            return function ($req, $res) {
                return $res->withStatus(404)->withJson(['error' => 'Endpoint does not exist']);
            };
        }
    ];
};