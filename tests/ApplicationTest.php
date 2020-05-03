<?php
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{

    public function testParseController()
    {
        $app = new mock\ApplicationMock();

        list($class, $controller, $args) = $app->parseControllerMock('/mock');
        $this->assertInstanceOf(mock\controllers\mock::class, $class);
        $this->assertSame('mock\\controllers\\mock', $controller);
        $this->assertSame([], $args);

        list($class, $controller, $args) = $app->parseControllerMock('/mock/path1/path2');
        $this->assertInstanceOf(mock\controllers\mock::class, $class);
        $this->assertSame('mock\\controllers\\mock', $controller);
        $this->assertSame(['path1', 'path2'], $args);

        list($class, $controller, $args) = $app->parseControllerMock('/path1/mock/path1/');
        $this->assertInstanceOf(mock\controllers\path1\mock::class, $class);
        $this->assertSame('mock\\controllers\\path1\\mock', $controller);
        $this->assertSame(['path1'], $args);
    }

}