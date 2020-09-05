<?php
namespace Kore;

class Log
{
    const LEVEL_DEBUG = 10;
    const LEVEL_INFO = 20;
    const LEVEL_WARN = 30;
    const LEVEL_ERROR = 40;

    /**
     * @var string
     */
    private static $logName;

    /**
     * @var int
     */
    private static $logLevel;

    /**
     * @param string $logName
     * @param int $logLevel
     * @return void
     */
    public static function init($logName = 'app', $logLevel = self::LEVEL_DEBUG)
    {
        self::$logName = $logName;
        self::$logLevel = $logLevel;
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function debug($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_DEBUG) {
            self::write('DEBUG', $msg, $obj);
        }
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function info($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_INFO) {
            self::write('INFO', $msg, $obj);
        }
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function warn($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_WARN) {
            self::write('WARN', $msg, $obj);
        }
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function error($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_ERROR) {
            self::write('ERROR', $msg, $obj);
        }
    }

    /**
     * @param string $level
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    private static function write($level, $msg, $obj)
    {
        $logfile = LOGS_DIR.'/'.self::$logName.'-'.date("Y-m-d").'.log';
        $msg .= PHP_EOL;
        if ($obj !== null) {
            ob_start();
            var_dump($obj);
            $msg .= ob_get_contents();
            ob_end_clean();
        }
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($trace[2]['class']) ? sprintf('%s%s%s', $trace[2]['class'], $trace[2]['type'], $trace[2]['function']) : '';
        $log = sprintf('[%s.%s][%s][%s][%s]%s', date('Y-m-d H:i:s'), explode('.', (string)microtime(true))[1], getmypid(), $level, $caller, $msg);
        //$log = sprintf('[%s.%s][%s][%s]%s', date('Y-m-d H:i:s', $tarray[0]), $tarray[1], getmypid(), $level, $msg);
        file_put_contents($logfile, $log, FILE_APPEND);
    }
}
