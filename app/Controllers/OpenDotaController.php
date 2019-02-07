<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Services\OpenDota;

class OpenDotaController
{
    private $dota;

    public function __construct(OpenDota $dota)
    {
        $this->dota = $dota;
    }

    public function apiCall(Request $req, Response $res, array $args): Response
    {
        $params = $req->getQueryParams();

        $interface  = $args['interface'];
        $identifier = $args['identifier'] ?? null;
        $option     = $args['option']     ?? null;

        try {
            $json = $this->dota->apiCall($interface, $identifier, $option);
            return $res->withHeader('Content-type', 'application/json')->write($json);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return $res->withStatus($code)->withJson([ 'error' => $code ]);
        }
    }
}