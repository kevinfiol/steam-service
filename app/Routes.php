<?php declare(strict_types = 1);

return [
    /** Steam Endpoints */
    '/steamAPI/{iface}/{command}/{version}/' => [['GET'], 'App\Controllers\SteamController:apiCall'],
    '/storeAPI/{command}/'                   => [['GET'], 'App\Controllers\SteamController:storeCall'],

    /** OpenDota Endpoints */
    '/players/{account_id}/[{option}]'       => [['GET'], 'App\Controllers\DotaController::players'],
];