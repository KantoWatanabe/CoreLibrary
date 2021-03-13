<?php
use PHPUnit\Framework\TestCase;

use Kore\Command;

class CommandTest extends TestCase
{
    public function testMain()
    {
        $commandName = 'mockCommand';
        $command = COMMANDS_NS."\\$commandName";
        $args = ['test'];
        $opts = ['env' => 'test'];

        $class = new $command();
        $class->main($command, $args, $opts);
        $this->assertSame(true, true);

        // 多重起動チェック
        $lockfile = TMP_DIR."/$commandName.lock";
        touch($lockfile);
        $class->main($command, $args, $opts);
        $this->assertSame(true, true);
        unlink($lockfile);

        // エラーのハンドリング
        $opts['error'] = '1';
        try {
            $class->main($command, $args, $opts);
        } catch (\Exception $e) {
            $this->assertSame('test error!', $e->getMessage());
        }

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
        $this->assertSame('default', $method->invoke($class, 1, 'default'));
        $this->assertSame(['test'], $method->invoke($class));
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
        $this->assertSame('default', $method->invoke($class, 'notfound', 'default'));
        $this->assertSame(['env' => 'test', 'error' => '1'], $method->invoke($class));
    }
}
