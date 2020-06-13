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

    public function testGet() {
        $url = self::$server->getServerRoot() . '/get';
        $params = ['param' => 'hoge'];
        $headers = ['X-Test-Header: fuga'];

        $client = new HttpClient;
        $res = $client->get($url, $params, $headers);

        $this->assertSame('hoge', $res['_GET']['param']);
        $this->assertSame('fuga', $res['HEADERS']['X-Test-Header']);
    }

    public function testPost() {
        $url = self::$server->getServerRoot() . '/post';
        $params = ['param' => 'hoge'];
        $headers = ['X-Test-Header: fuga'];

        $client = new HttpClient;
        $res = $client->post($url, $params, $headers);

        $this->assertSame('hoge', $res['_POST']['param']);
        $this->assertSame('fuga', $res['HEADERS']['X-Test-Header']);
    }
}