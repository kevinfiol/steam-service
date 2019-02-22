# Steam Service API

Main Instance here: [https://steam-serv.herokuapp.com/](https://steam-serv.herokuapp.com/)

The application expects a configuration file called `Config.php` that should be placed in `app/Config.php`. This config file should return a simple associative array including:

* `STEAM_API` -> your Steam Web API key
* `HERO_PATH` -> the path to heroes.json
* `LOGS_PATH` -> your Monolog logfile path

Here is a sample Config file:

```php
<?php declare(strict_types = 1);

return [
    'STEAM_API' => // Your Steam Web API key
    'HERO_PATH' => '/../data/heroes.json',
    'LOGS_PATH' => '/../logs/steam-service.log'
];
```

## Endpoints

All endpoints are described in `app/Routes.php`.

### Steam Web API

```
/steamAPI/{iface}/{command}/{version}/
```

Documentation for the Steam Web API can be found here: [Steam Web API](https://developer.valvesoftware.com/wiki/Steam_Web_API). An API key is needed to use these methods. These can be obtained here: [Steam API Key - Steam Community](https://steamcommunity.com/dev/apikey).

### Steam Storefront API

```
/storeAPI/{command}/
```

Documentation for the Steam Storefront API can be found here: [Storefront API](https://wiki.teamfortress.com/wiki/User:RJackson/StorefrontAPI).

### OpenDota API

```
/openDota/{interface}[/{identifier}[/{option}]]
```

Documentation for the OpenDota API can be found here: [OpenDota API](https://docs.opendota.com/).

### Custom API

Only one method is currently available:

```
/app/getDotaPlayer/{steam_id}
```

#### getDotaPlayer

`steam_id` can be a Steam vanity profile id (ex. `https://steamcommunity.com/id/[your_profile_id]/`) or your Steam 64-bit ID (these can be found [here](https://steamid.io/)).

This endpoint returns selective profile and statistical data on a player's Dota profile.