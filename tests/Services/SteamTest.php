<?php declare(strict_types = 1);

namespace App\Tests\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

use PHPUnit\Framework\TestCase;
use App\Services\Steam;

class SteamDotaTest extends TestCase
{
    public function testCalls()
    {
        $mock = new MockHandler([
            new Response(200,
                ['Content-type', 'application/json'],
                '{ "content": "apicall" }'
            ),
            new Response(200,
                ['Content-type', 'application/json'],
                '{ "content": "storecall" }'
            )
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $steam   = new Steam('fake_api_key', $client);

        $apiCallRes = $steam->apiCall('one', 'fish', 'two');
        $apiRes = json_decode($apiCallRes, true);

        $storeCallRes = $steam->storeCall('one');
        $storeRes = json_decode($storeCallRes, true);

        $this->assertArrayHasKey('content', $apiRes);
        $this->assertArrayHasKey('content', $storeRes);

        $this->assertEquals('apicall', $apiRes['content']);
        $this->assertEquals('storecall', $storeRes['content']);
    }
}