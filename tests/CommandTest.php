<?php
use PHPUnit\Framework\TestCase;

use Kore\Command;

class CommandTest extends TestCase
{
    public function testMain()
    {
        $command = COMMANDS_NS.'mockCommand';
        $args = ['test'];
        $opts = ['env' => 'test'];

        $class = new $command();
        $class->main($command, $args, $opts);
        $this->assertSame(true, true);

        return $class;
    }

    /**
     * @depends testMain
     */
    public function testCommandName($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'commandName');
        $method->setAccessible(true);
        $this->assertSame('mockCommand', $method->invoke($class));
    }

    /**
     * @depends testMain
     */
    public function testGetArg($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getArg');
        $method->setAccessible(true);
        $this->assertSame('test', $method->invoke($class, 0));
        $this->assertSame(null, $method->invoke($class, 1));
    }

    /**
     * @depends testMain
     */
    public function testGetOpt($class)
    {
        $method = new \ReflectionMethod(get_class($class), 'getOpt');
        $method->setAccessible(true);
        $this->assertSame('test', $method->invoke($class, 'env'));
        $this->assertSame(null, $method->invoke($class, 'notfound'));
    }

}