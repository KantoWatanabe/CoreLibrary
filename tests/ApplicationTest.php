<?php
use PHPUnit\Framework\TestCase;

use Kore\Application;

class ApplicationTest extends TestCase
{
    public function testSetBasePath()
    {
        $app = new Application();
        $app->setBasePath('base');
        $class = new ReflectionClass('Kore\\Application');
        $property = $class->getProperty('basePath');
        $property->setAccessible(true);
        $this->assertSame('base', $property->getValue($app));
    }

    public function testSetDefaultController()
    {
        $app = new Application();
        $app->setDefaultController('base');
        $class = new ReflectionClass('Kore\\Application');
        $property = $class->getProperty('defaultController');
        $property->setAccessible(true);
        $this->assertSame('base', $property->getValue($app));
    }

    public function testSetNotFound()
    {
        $app = new Application();
        $app->setNotFound(function () {
            // NOP
        });
        $class = new ReflectionClass('Kore\\Application');
        $property = $class->getProperty('notFound');
        $property->setAccessible(true);
        $this->assertSame(true, is_callable($property->getValue($app)));
    }

    public function testRun()
    {
        $app = new Application();
        $app->setBasePath('base');
        $app->setNotFound(function () {
            // NOP
        });

        $_SERVER['REQUEST_URI'] = '/base/mock/?query1=test&query2=test';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $app->run();
        $this->assertSame(true, true);

        $_SERVER['REQUEST_URI'] = '/notFound';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $app->run();
        $this->assertSame(404, http_response_code());
    }
    
    public function testParseController()
    {
        $app = new Application();
        $method = new \ReflectionMethod(get_class($app), 'parseController');
        $method->setAccessible(true);

        list($class, $controller, $args) = $method->invoke($app, 'mock');
        $this->assertInstanceOf(mock\controllers\mock::class, $class);
        $this->assertSame('mock\\controllers\\mock', $controller);
        $this->assertSame([], $args);

        list($class, $controller, $args) = $method->invoke($app, 'mock/path1/path2');
        $this->assertInstanceOf(mock\controllers\mock::class, $class);
        $this->assertSame('mock\\controllers\\mock', $controller);
        $this->assertSame(['path1', 'path2'], $args);

        list($class, $controller, $args) = $method->invoke($app, 'path1/mock/path1');
        $this->assertInstanceOf(mock\controllers\path1\mock::class, $class);
        $this->assertSame('mock\\controllers\\path1\\mock', $controller);
        $this->assertSame(['path1'], $args);
    }

    public function testRunCmd()
    {
        $app = new Application();

        $app->runCmd([null, 'mockCommand']);
        $this->assertSame(true, true);
    }

    public function testNoCmdName()
    {
        $app = new Application();

        $this->expectExceptionMessage('Unable to find command name');
        $app->runCmd([null]);
    }

    public function testNoCmdClass()
    {
        $app = new Application();

        $this->expectExceptionMessage('Unable to load command class');
        $app->runCmd([null, 'notFound']);
    }

    public function testParseConmmand()
    {
        $app = new Application();
        $method = new \ReflectionMethod(get_class($app), 'parseCommand');
        $method->setAccessible(true);

        list($class, $command, $args, $opts) = $method->invoke($app, [null, 'mockCommand', 'hoge', '--env=test']);
        $this->assertInstanceOf(mock\commands\mockCommand::class, $class);
        $this->assertSame('mock\\commands\\mockCommand', $command);
        $this->assertSame(['hoge'], $args);
        $this->assertSame(['env' => 'test'], $opts);
    }
}
