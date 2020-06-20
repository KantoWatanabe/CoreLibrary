<?php
use PHPUnit\Framework\TestCase;

use Kore\HttpClient;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

class HttpClientTest extends TestCase
{
    protected static $server;
    
    protected function setUp(): void
    {
        self::$server = new MockWebServer;
        self::$server->start();
    }

    public static function tearDownAfterClass(): void
    {
        self::$server->stop();
    }

    public function testGet()
    {
        $url = self::$server->getServerRoot() . '/get';
        $params = ['param' => 'hoge'];
        $headers = ['X-Test-Header: fuga'];

        $client = new HttpClient;
        $res = $client->get($url, $params, $headers);
        $body = json_decode($res->getBody(), true);

        $this->assertSame('hoge', $body['_GET']['param']);
        $this->assertSame('fuga', $body['HEADERS']['X-Test-Header']);
    }

    public function testPost()
    {
        $url = self::$server->setResponseOfPath(
            '/post',
            new Response(
                'response body',
                ['Cache-Control' => 'no-cache'],
                201
            )
        );
        $params = ['param' => 'hoge'];
        $headers = ['X-Test-Header: fuga'];

        $client = new HttpClient;
        $res = $client->post($url, $params, $headers);
        preg_match('/Cache-Control: ([-a-zA-Z]*)/', $res->getHeader(), $matches);

        $this->assertSame(201, $res->getHttpCode());
        $this->assertSame('no-cache', $matches[1]);
        $this->assertSame('response body', $res->getBody());
    }
}
