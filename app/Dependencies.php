<?php declare(strict_types = 1);

use GuzzleHttp\Client;

use App\Controllers\SteamController;
use App\Services\Steam;

return function (array $config) {
    return [
        'GuzzleHttp\Client' => function () {
            return new Client();
        },

        'App\Services\Steam' => function ($c) use ($config) {
            $client = $c->get('GuzzleHttp\Client');
            return new Steam($config['STEAM_API'], $client);
        },

        'App\Controllers\SteamController' => function ($c) {
            $steam = $c->get('App\Services\Steam');
            return new SteamController($steam);
        }
    ];
};