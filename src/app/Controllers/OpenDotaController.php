<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Services\OpenDota;
use App\Utility\JSONWriter;

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
            return JSONWriter::writeString($response, $json);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return JSONWriter::writeArray($response, ['error' => $code]);
        }
    }
}