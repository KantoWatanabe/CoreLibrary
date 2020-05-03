<?php
use PHPUnit\Framework\TestCase;

use Kore\Log;

class LogTest extends TestCase
{
    public function testInit()
    {
        Log::init('test');
        
    }
}