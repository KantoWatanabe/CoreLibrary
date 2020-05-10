<?php
use PHPUnit\Framework\TestCase;

use Kore\Log;

class LogTest extends TestCase
{
    public function testInit()
    {
        $class = new ReflectionClass('Kore\\Log');
        $property = $class->getProperty('logName');
        $property->setAccessible(true);

        Log::init('test');
        $this->assertSame('test', $property->getValue());
    }

    public function testDebug()
    {  
        Log::init('test');
        $logfile = LOGS_DIR.'test-'.date("Y-m-d").'.log';
        @unlink($logfile);

        Log::debug('test!');
        $this->assertFileExists($logfile);
    }


}