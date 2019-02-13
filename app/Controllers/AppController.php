<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;

use App\Services\Steam;
use App\Services\OpenDota;

class AppController
{
    private $steam;
    private $dota;
    private $heroDict;

    public function __construct(Steam $steam, OpenDota $dota, array $heroDict)
    {
        $this->steam    = $steam;
        $this->dota     = $dota;
        $this->heroDict = $heroDict;
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

            // Get Top 5 from $heroes
            $heroes = array_map(function ($i) use ($heroes) {
                return $heroes[$i];
            }, [0, 1, 2, 3, 4]);

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