<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;

use App\Services\Steam;
use App\Services\OpenDota;
use App\Database\Database;

class AppController
{
    private $steam;
    private $dota;
    private $db;
    private $heroDict;

    public function __construct(Steam $steam, OpenDota $dota, Database $db, array $heroDict)
    {
        $this->steam    = $steam;
        $this->dota     = $dota;
        $this->db       = $db;
        $this->heroDict = $heroDict;
    }

    public function getSteamAppDetails(Request $req, Response $res, array $args): Response
    {
        $app = null;
        $params = $req->getQueryParams();

        if (!isset($params['appids']))
            return $res->withJson(['error' => 'no appids provided']);

        $appids = $params['appids'];
        $rows = $this->db->getRows('SteamApp', ['steam_appid' => $appids]);

        if (count($rows) !== 0) {
            $app = $rows[0]->getValues();
        } else {
            $json = $this->steam->storeCall('appdetails', $params);
            $data = json_decode($json, true)[$appids];
            $appData = $data['data'] ?? null;

            $newAppRow = [
                'steam_appid'  => $appData['steam_appid'],
                'name'         => $appData['name'],
                'header_image' => $appData['header_image'],
                'is_free'      => $appData['is_free'],
                'platforms'    => json_encode($appData['platforms']),
                'categories'   => array_map(function ($c) {
                    return $c['id'];
                }, $appData['categories'])
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
            $app['platforms'] = $appData['platforms'];
        }

        return $res->withJson($app);
    }

    public function getAllSteamCategories(Request $req, Response $res): Response
    {
        $rows = $this->db->getRows('SteamCategory');
        $categories = [];

        foreach ($rows as $r) {
            $category = $r->getValues();
            $categories[ $category['category_id'] ] = $category['description'];
        }

        return $res->withJson($categories);
    }

    public function getDotaPlayer(Request $req, Response $res, array $args): Response
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

            return $res->withJson([
                'player' => $player,
                'totals' => $totals,
                'heroes' => $heroes
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return $res->withStatus($code)->withJson(['error' => $code]);
        }
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