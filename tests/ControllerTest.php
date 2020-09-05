<?php
use PHPUnit\Framework\TestCase;

use Kore\Controller;

class ControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['query'] = 'hoge';
        $_POST['post'] = 'fuga';
        $_COOKIE['cookie'] = 'piyo';
        $_SERVER['HTTP_X_TEST_HEADER'] = 'hoge';
    }

    public function testMain()
    {
        $controller = CONTROLLERS_NS.'\\mock';
        $args = ['123'];

        $class = new $controller();
        $class->main($controller, $args);
        $this->assertSame(true, true);

        return $class;
    }

    /**
     * @depends testMain
     */
    public function testGetMethod($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getMethod');
        $method->setAccessible(true);
        $this->assertSame('GET', $method->invoke($class));
    }

    /**
     * @depends testMain
     */
    public function testGetHeader($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getHeader');
        $method->setAccessible(true);
        $this->assertSame('hoge', $method->invoke($class, 'X-Test-Header'));
        $this->assertSame(null, $method->invoke($class, 'X-Test-NotFoud'));
        $this->assertSame('default', $method->invoke($class, 'X-Test-NotFoud', 'default'));
    }

    /**
     * @depends testMain
     */
    public function testGetQuery($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getQuery');
        $method->setAccessible(true);
        $this->assertSame('hoge', $method->invoke($class, 'query'));
        $this->assertSame(null, $method->invoke($class, 'notfound'));
        $this->assertSame('default', $method->invoke($class, 'notfound', 'default'));
    }

    /**
     * @depends testMain
     */
    public function testGetPost($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getPost');
        $method->setAccessible(true);
        $this->assertSame('fuga', $method->invoke($class, 'post'));
        $this->assertSame(null, $method->invoke($class, 'notfound'));
        $this->assertSame('default', $method->invoke($class, 'notfound', 'default'));
    }

    /**
     * @depends testMain
     */
    public function testGetCookie($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getCookie');
        $method->setAccessible(true);
        $this->assertSame('piyo', $method->invoke($class, 'cookie'));
        $this->assertSame(null, $method->invoke($class, 'notfound'));
        $this->assertSame('default', $method->invoke($class, 'notfound', 'default'));
    }

    /**
     * @depends testMain
     */
    public function testGetArg($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getArg');
        $method->setAccessible(true);
        $this->assertSame('123', $method->invoke($class, 0));
        $this->assertSame(null, $method->invoke($class, 1));
        $this->assertSame('default', $method->invoke($class, 1, 'default'));
    }

    /**
     * @depends testMain
     */
    public function testExtractView($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'extractView');
        $method->setAccessible(true);
        $this->assertSame('hoge', $method->invoke($class, 'mock', ['test' => 'hoge']));
    }

    /**
     * @depends testMain
     * @runInSeparateProcess
     */
    public function testRespondJson($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'respondJson');
        $method->setAccessible(true);
        ob_start();
        $method->invoke($class, ['test' => 'hoge']);
        $actual = ob_get_contents();
        ob_end_clean();
        $this->assertSame('{"test":"hoge"}', $actual);
    }

    /**
     * @depends testMain
     * @runInSeparateProcess
     */
    public function testRedirect($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'redirect');
        $method->setAccessible(true);
        $method->invoke($class, 'https://github.com/KantoWatanabe/KoreLibrary');
        $this->assertSame(true, true);
    }
}
