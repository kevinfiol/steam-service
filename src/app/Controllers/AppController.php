<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Config\Config;
use App\Services\Steam;
use App\Services\OpenDota;
use App\Database\Database;
use App\Utility\JSONWriter;

class AppController
{
    private $steam;
    private $dota;
    private $db;
    private $heroDict;

    public function __construct(Steam $steam, OpenDota $dota, Database $db, Config $config)
    {
        $this->steam = $steam;
        $this->dota  = $dota;
        $this->db    = $db;

        // Load Hero Dict from Filesystem
        $appConfig = $config->get('app');
        $hero_path = $appConfig['hero_path'];

        if (file_exists($hero_path) && is_readable($hero_path)) {
            $contents = file_get_contents($hero_path);
            if (!$contents) throw new \Exception('Cannot read file contents.');
            $this->heroDict = json_decode($contents, true);
        } else {
            throw new \Exception('Heroes JSON cannot be found!');
        }
    }

    public function serverWakeup(Request $request, Response $response): Response
    {
        return JSONWriter::writeArray($response, ['message' => 'OK']);
    }

    public function getSteamAppDetails(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        if (!isset($params['appids'])) {
            $payload = ['error' => 'no appids provided'];
        } else {
            $appids = $params['appids'];
            $rows = $this->db->getRows('SteamApp', ['steam_appid' => $appids]);

            if (count($rows) > 0) {
                $steam_app = $rows[0]->getValues();
                $payload = $steam_app;
            } else {
                $payload = $this->getSteamApp($params);
            }
        }

        return JSONWriter::writeArray($response, $payload);
    }

    public function getCommonApps(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $steamids = $params['steamids'];
        $steamids = explode(',', $steamids);

        $users = [];
        $results = array_map(function($steamid) {
            return $this->steam->apiCall('IPlayerService', 'GetOwnedGames', 'v0001', [
                'steamid' => $steamid,
                'include_appinfo' => 1,
                'include_played_free_games' => 1
            ]);
        }, $steamids);

        foreach ($results as $json) {
            $data = json_decode($json, true);
            $games = $data['response']['games'];

            $appids = array_map(function($g) { return strval($g['appid']); }, $games);
            $users[] = $appids;
        }

        $first = array_pop($users);
        $commonAppIds = array_reduce($users, function($acc, $next) {
            return array_intersect($next, $acc);
        }, $first);

        $rows = $this->db->getRows('SteamApp', ['steam_appid' => $commonAppIds]);
        $appsFromDb = array_map(function($r) { return $r->getValues(); }, $rows);
        
        $idsFromDb  = array_map(function($a) { return strval($a['steam_appid']); }, $appsFromDb);
        $appIdsToFetch = array_diff($commonAppIds, $idsFromDb);

        if (count($appIdsToFetch) > 0) {
            // Fetch unfetched Game Data
            $fetchedApps = array_map(function($i) {
                return $this->getSteamApp(['appids' => $i]);
            }, $appIdsToFetch);

            // Clear Empty Entries
            $fetchedApps = array_filter($fetchedApps, function($a) {
                return count($a) > 0;
            });
        } else {
            $fetchedApps = [];
        }

        $commonApps = array_merge($appsFromDb, $fetchedApps);

        // Sort Apps By Name
        usort($commonApps, function($a, $b) {
            return strtoupper($a['name']) > strtoupper($b['name']);
        });

        return JSONWriter::writeArray($response, $commonApps);
    }

    public function getAllSteamCategories(Request $request, Response $response): Response
    {
        $rows = $this->db->getRows('SteamCategory');
        $categories = [];

        foreach ($rows as $r) {
            $category = $r->getValues();
            $categories[ $category['category_id'] ] = $category['description'];
        }

        return JSONWriter::writeArray($response, $categories);
    }

    public function getDotaPlayer(Request $request, Response $response, array $args): Response
    {
        try {
            $steam_id = strval($args['steam_id']);

            // If Vanity ID, resolve Steam 64 ID
            if (!is_numeric($steam_id)) $steam_id = $this->resolveVanityUrl($steam_id);
            $steam32_id = $this->convertId('to32', $steam_id);
            
            $player  = json_decode($this->dota->apiCall('players', $steam32_id), true);
            $totals  = json_decode($this->dota->apiCall('players', $steam32_id, 'totals'), true);
            $heroes  = json_decode($this->dota->apiCall('players', $steam32_id, 'heroes'), true);
            $winloss = json_decode($this->dota->apiCall('players', $steam32_id, 'wl'), true);

            $player['wl'] = $winloss;

            $totals = array_reduce($totals, function ($acc, $x) {
                $key = $x['field'];
                $x['field'] = $this->capitalizeWords($x['field']);
                $acc[$key] = $x;
                return $acc;
            }, []);

            // Get Top 6 from $heroes
            $heroes = array_map(function ($i) use ($heroes) {
                return $heroes[$i];
            }, [0, 1, 2, 3, 4, 5]);

            $heroes = array_map(function ($hero) {
                // Append properties from Heroes Dictionary
                $hero = array_merge($hero, $this->heroDict[$hero['hero_id']]);
                // Append img url base
                $hero['img']  = "https://api.opendota.com{$hero['img']}";
                $hero['icon'] = "https://api.opendota.com{$hero['icon']}";

                return $hero;
            }, $heroes);


            return JSONWriter::writeArray($response, [
                'player' => $player,
                'totals' => $totals,
                'heroes' => $heroes
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return JSONWriter::writeArray($response, ['error' => $code]);
        }
    }

    public function getFriends(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $steamid = $params['steamid'];

        if (!is_numeric($steamid))
            $steamid = $this->resolveVanityUrl($steamid);

        $apiRes = $this->steam->apiCall('ISteamUser', 'GetFriendList', 'v0001', [
            'steamid' => $steamid,
            'relationship' => 'friend'
        ]);

        $friendData     = json_decode($apiRes, true)['friendslist']['friends'];
        $friendIds      = array_map(function($f) { return $f['steamid']; }, $friendData);
        $friendIdString = implode(',', $friendIds);

        $summaries = $this->getPlayerSummaries($friendIdString);
        $friends = array_map(function ($s) {
            return [
                'steamid'     => $s['steamid'],
                'personaname' => $s['personaname'],
                'profileurl'  => $s['profileurl'],
                'avatar'      => $s['avatarmedium'],
            ];
        }, $summaries);

        // Sort Friends By Name
        usort($friends, function($a, $b) {
            return strtoupper($a['personaname']) > strtoupper($b['personaname']);
        });

        return JSONWriter::writeArray($response, $friends);
    }

    public function getSteamID(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $identifier = $params['identifier'];

        if (!is_numeric($identifier))
            $steamid = $this->resolveVanityUrl($identifier);
        else
            $steamid = $identifier;

        return JSONWriter::writeArray($response, ['steamid' => $steamid]);
    }

    private function getSteamApp(array $params): array
    {
        $app    = null;
        $appids = $params['appids'];

        // Retrieve Data from Store API
        $json = $this->steam->storeCall('appdetails', $params);
        $data = json_decode($json, true)[$appids];
        $appData = $data['data'] ?? null;

        // If null, or steam_appid mismatch, return empty object
        if (!$appData || $appids != $appData['steam_appid']) {
            return [];
        }

        // Create new App
        $newAppRow = [
            'steam_appid'  => $appData['steam_appid'],
            'name'         => $appData['name'],
            'header_image' => $appData['header_image'],
            'is_free'      => $appData['is_free'],
            'platforms'    => json_encode($appData['platforms']),
            'categories'   => array_map(function ($c) {
                return $c['id'];
            }, $appData['categories'] ?? [])
        ];

        // Add new App to Database
        $this->db->addRow('SteamApp', $newAppRow);

        // Add new Categories to Database
        foreach ($appData['categories'] as $c) {
            $rows = $this->db->getRows('SteamCategory', ['category_id' => $c['id']]);

            if (count($rows) === 0) {
                // Add new Category
                $newCatRow = ['category_id' => $c['id'], 'description' => $c['description']];
                $this->db->addRow('SteamCategory', $newCatRow);
            }
        }

        // Prepare App to return as Response to User
        $app = $newAppRow;

        // revert platforms back to assoc array instead of json string
        $app['platforms'] = $appData['platforms'];

        return $app;
    }

    private function getPlayerSummaries(string $steamids): array
    {
        $res = $this->steam->apiCall('ISteamUser', 'GetPlayerSummaries', 'v0002', [
            'steamids' => $steamids
        ]);

        $summaries = json_decode($res, true);
        $players = $summaries['response']['players'] ?? [];

        return $players;
    }

    private function resolveVanityUrl(string $steam_id): string
    {
        $res = $this->steam->apiCall('ISteamUser', 'ResolveVanityURL', 'v0001', [
            'vanityurl' => $steam_id
        ]);

        $res = json_decode($res, true);

        if ($res['response']['success'] === 1) {
            return $res['response']['steamid'];
        }

        // The reason you just returned instead of throwing an Exception is because
        // what if the user has a vanityURL that is 17 digits?
        return $steam_id;
    }

    private function convertId(string $format, string $id): string
    {
        $base = '76561197960265728';

        switch ($format) {
            case 'to32':
                return bcsub($id, $base);
            case 'to64':
                return bcadd($id, $base);
            default:
                return $id;
        }
    }

    private function capitalizeWords(string $words): string
    {
        $words = str_replace('_', ' ', $words);
        $list = explode(' ', $words);

        $list = array_map(function ($x) {
            return ucfirst($x);
        }, $list);

        $capitalized = implode(' ', $list);
        return $capitalized;
    }
}