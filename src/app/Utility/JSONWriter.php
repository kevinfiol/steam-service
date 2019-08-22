<?php declare(strict_types = 1);

namespace App\Utility;

use Psr\Http\Message\ResponseInterface as Response;

class JSONWriter
{
    public static function writeString(Response $response, string $json)
    {
        $response->getBody()->write($json);
        return $response->withHeader('Content-type', 'application/json');
    }

    public static function writeArray(Response $response, array $payload)
    {
        $json = json_encode($payload);
        $response->getBody()->write($json);
        return $response->withHeader('Content-type', 'application/json');
    }
}