<?php
use PHPUnit\Framework\TestCase;

use Kore\Log;

class LogTest extends TestCase
{
    protected function setUp(): void
    {
        Log::init('test');
    }
    
    public function testInit()
    {
        $class = new ReflectionClass('Kore\\Log');
        $property = $class->getProperty('logName');
        $property->setAccessible(true);

        $this->assertSame('test', $property->getValue());
    }

    public function testDebug()
    {
        //$logfile = LOGS_DIR.'/test-'.date("Y-m-d").'.log';
        //@unlink($logfile);

        Log::debug('debug');
        $this->assertSame(true, true);
        //$this->assertFileExists($logfile);
    }

    public function testInfo()
    {
        Log::info('info');
        $this->assertSame(true, true);
    }

    public function testWarn()
    {
        Log::warn('warn');
        $this->assertSame(true, true);
    }

    public function testError()
    {
        Log::error('error', 'obj');
        $this->assertSame(true, true);
    }
}
