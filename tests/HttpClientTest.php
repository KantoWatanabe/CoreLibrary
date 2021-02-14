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
        $client->setConnectTimeout(10);
        $client->setTimeout(10);
        $res = $client->get($url, $params, $headers);
        $body = $res->getJsonBody();

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
        $this->assertSame('no-cache', $res->getHeaderLine('Cache-Control'));
        $this->assertSame(null, $res->getHeaderLine('Not-Found'));
        $this->assertSame('response body', $res->getBody());
    }

    public function testPut()
    {
        $url = self::$server->getServerRoot() . '/put';
        $params = ['param' => 'hoge'];
        $headers = ['Content-Type: application/json; charset=UTF-8'];

        $client = new HttpClient;
        $res = $client->put($url, $params, $headers);
        $body = json_decode($res->getBody(), true);

        $this->assertSame('PUT', $body['METHOD']);
        $this->assertSame('{"param":"hoge"}', $body['INPUT']);
    }

    public function testPatch()
    {
        $url = self::$server->getServerRoot() . '/patch';
        $params = ['param' => 'hoge'];
        $headers = ['Content-Type: application/json'];

        $client = new HttpClient;
        $res = $client->patch($url, $params, $headers);
        $body = json_decode($res->getBody(), true);

        $this->assertSame('PATCH', $body['METHOD']);
        $this->assertSame('{"param":"hoge"}', $body['INPUT']);
    }

    public function testDelete()
    {
        $url = self::$server->getServerRoot() . '/delete';

        $client = new HttpClient;
        $res = $client->delete($url);
        $body = json_decode($res->getBody(), true);

        $this->assertSame('DELETE', $body['METHOD']);
    }

    public function testAcquisitionFailed()
    {
        $this->expectExceptionMessage('Communication Error');
        $client = new HttpClient;
        $res = $client->get(null);
    }

    /**
     * @dataProvider provider
     */
    public function testBuildHeader($key, $expected)
    {
        $client = new HttpClient;
        $method = new \ReflectionMethod(get_class($client), 'buildHeader');
        $method->setAccessible(true);
        
        $this->assertSame($expected, $method->invoke($client, $key));
    }

    public function provider()
    {
        return [
            [['X-Test-Header: fuga', 'Content-Type: application/json'], ['X-Test-Header: fuga', 'Content-Type: application/json']],
            [['X-Test-Header' => 'fuga', 'Content-Type' => 'application/json'], ['X-Test-Header: fuga', 'Content-Type: application/json']],
        ];
    }
}
