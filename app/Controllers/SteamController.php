<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Services\Steam;

class SteamController
{
    private $steam;

    public function __construct(Steam $steam)
    {
        $this->steam = $steam;
    }

    public function apiCall(Request $req, Response $res, array $args): Response
    {
        $params = $req->getQueryParams();
        ['iface' => $iface, 'command' => $command, 'version' => $version] = $args;

        try {
            $json = $this->steam->apiCall($iface, $command, $version, $params);
            return $res->withHeader('Content-type', 'application/json')->write($json);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return $res->withStatus($code)->withJson([ 'error' => $code ]);
        }
    }

    public function storeCall(Request $req, Response $res, array $args): Response
    {
        $params  = $req->getQueryParams();
        $command = $args['command'];

        try {
            $json = $this->steam->storeCall($command, $params);
            return $res->withHeader('Content-type', 'application/json')->write($json);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return $res->withStatus($code)->withJson([ 'error' => $code ]);
        }
    }
}