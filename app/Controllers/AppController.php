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
        return $res->withJson([
            'heroes' => 'fuckyou'
        ]);
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