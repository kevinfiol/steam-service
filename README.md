# Steam Service API

Main Instance here: [https://steam-serv.herokuapp.com/](https://steam-serv.herokuapp.com/)

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