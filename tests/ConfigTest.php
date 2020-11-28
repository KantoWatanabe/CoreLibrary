<?php
use PHPUnit\Framework\TestCase;

use Kore\Config;

class ConfigTest extends TestCase
{
    public function testCreateNoConfig()
    {
        $this->expectExceptionMessage('Unable to find config file');
        Config::create("noconfig");
    }

    public function testCreate()
    {
        Config::create("test");
        $this->assertSame(true, true);
    }

    /**
     * @dataProvider provider
     */
    public function testGet($key, $expected)
    {
        $this->assertSame($expected, Config::get($key));
        $this->assertIsArray(Config::get());
    }

    public function provider()
    {
        return [
            ["test", "test config"],
            ["common", "common config"],
            ["noconfig", null],
            ["hoge.fuga.piyo", "hogefugapiyo"],
            ["hoge.fuga", array("piyo" => "hogefugapiyo")],
            ["hoge.fuga.piyo2", null],
        ];
    }
}
