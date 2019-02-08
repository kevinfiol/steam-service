<?php declare(strict_types = 1);

namespace App\Tests\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

use PHPUnit\Framework\TestCase;
use App\Services\OpenDota;

class OpenDotaTest extends TestCase
{
    public function testGet()
    {
        $mock = new MockHandler([
            new Response(200,
                ['Content-type', 'application/json'],
                '{ "content": "json" }'
            )
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $dota    = new OpenDota($client);
        $res     = $dota->apiCall('foo', 'bar', 'fish');
        $content = json_decode($res, true);

        $this->assertArrayHasKey('content', $content);
        $this->assertEquals('json', $content['content']);
    }
}