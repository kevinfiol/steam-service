<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Services\Steam;
use App\Utility\JSONWriter;

class SteamController
{
    private $steam;

    public function __construct(Steam $steam)
    {
        $this->steam = $steam;
    }

    public function apiCall(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        ['iface' => $iface, 'command' => $command, 'version' => $version] = $args;

        try {
            $json = $this->steam->apiCall($iface, $command, $version, $params);
            return JSONWriter::writeString($response, $json);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return JSONWriter::writeArray($response, ['error' => $code]);
        }
    }

    public function storeCall(Request $request, Response $response, array $args): Response
    {
        $params  = $request->getQueryParams();
        $command = $args['command'];

        try {
            $json = $this->steam->storeCall($command, $params);
            return JSONWriter::writeString($response, $json);
        } catch (\Exception $e) {
            $code = $e->getCode();
            return JSONWriter::writeArray($response, ['error' => $code]);
        }
    }
}