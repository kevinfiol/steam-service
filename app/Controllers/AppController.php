<?php declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AppController
{

    public function __construct()
    {
    }

    public function getDotaPlayer(Request $req, Response $res, array $args): Response
    {
        return $res->withStatus(200)->write('hello world');
    }
}