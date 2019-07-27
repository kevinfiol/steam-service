<?php declare(strict_types = 1);

return [
    /** Steam Endpoints */
    '/steamAPI/{iface}/{command}/{version}/' => [['GET'], 'App\Controllers\SteamController:apiCall'],
    '/storeAPI/{command}/'                   => [['GET'], 'App\Controllers\SteamController:storeCall'],

    /** OpenDota Endpoints */
    '/openDota/{interface}[/{identifier}[/{option}]]' => [['GET'], 'App\Controllers\OpenDotaController:apiCall'],

    /** Application Endpoints */
    '/app/getDotaPlayer/{steam_id}' => [['GET'], 'App\Controllers\AppController:getDotaPlayer'],
    '/app/getSteamAppDetails/' => [['GET'], 'App\Controllers\AppController:getSteamAppDetails'],
    '/app/getAllSteamCategories/' => [['GET'], 'App\Controllers\AppController:getAllSteamCategories'],
    '/app/getFriends/' => [['GET'], 'App\Controllers\AppController:getFriends'],
    '/app/getCommonApps/{steamids}' => [['GET'], 'App\Controllers\AppController:getCommonApps']
];