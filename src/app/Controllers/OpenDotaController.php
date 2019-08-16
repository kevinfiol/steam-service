<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Services\OpenDota;

class OpenDotaController
{
    private $dota;

    public function __construct(OpenDota $dota)
    {
        $this->dota = $dota;
    }

    public function apiCall(Request $request, Response $response, array $args): Response
    {
        $interface  = $args['interface'];
        $identifier = $args['identifier'] ?? null;
        $option     = $args['option']     ?? null;

        try {
            $json = $this->dota->apiCall($interface, $identifier, $option);
            $response->getBody()->write($json);
            return $response->withHeader('Content-type', 'application/json');
        } catch (\Exception $e) {
            $code = $e->getCode();
            $json = json_encode(['error' => $code]);
            $response->getBody()->write($json);
            return $response->withStatus($code);
        }
    }
}