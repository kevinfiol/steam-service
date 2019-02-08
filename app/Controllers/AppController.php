<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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
            $steam_id = $args['steam_id'];

            // If Vanity ID, resolve Steam 64 ID
            if (!is_numeric($steam_id)) $steam_id = $this->resolveVanityUrl($steam_id);
            $steam32_id = $this->convertId('to32', $steam_id);
            
            $player = json_decode($this->dota->apiCall('players', $steam32_id), true);
            $totals = json_decode($this->dota->apiCall('players', $steam32_id, 'totals'), true);
            $heroes = json_decode($this->dota->apiCall('players', $steam32_id, 'heroes'), true);

            // Transform Totals
            $totals = array_reduce($totals, function ($acc, $x) {
                $acc[ $x['field'] ] = $x;
                return $acc;
            }, []);

            // Get Top 5 from $heroes
            $heroes = array_map(function ($i) use ($heroes) {
                return $heroes[$i];
            }, [0, 1, 2, 3, 4]);

            // Append properties from Heroes Dictionary
            $heroes = array_map(function ($hero) {
                return array_merge($hero, $this->heroDict[$hero['hero_id']]);
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
}