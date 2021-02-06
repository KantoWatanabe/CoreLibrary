<?php
use PHPUnit\Framework\TestCase;

class helpersTest extends TestCase
{
    /**
     * @dataProvider  url_add_query_provider
     */
    public function test_url_add_query($url, $params, $expected)
    {
        $this->assertSame($expected, url_add_query($url, $params));
    }

    public function url_add_query_provider()
    {
        return [
            ['url', ['q1' => 'test1'], 'url?q1=test1'],
            ['url?q1=test1', ['q2' => 'test2'], 'url?q1=test1&q2=test2'],
        ];
    }

    /**
     * @dataProvider  array_get_recursive_provider
     */
    public function test_array_get_recursive($array, $key, $expected)
    {
        $this->assertSame($expected, array_get_recursive($array, $key));
    }

    public function array_get_recursive_provider()
    {
        return [
            [['key1' => ['key2' => 'test']], 'key1.key2', 'test'],
            [['key1' => ['key2' => 'test']], 'key1.key3', null],
        ];
    }
}
