<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

/**
 * Log class
 *
 */
class Log
{
    const LEVEL_DEBUG = 10;
    const LEVEL_INFO = 20;
    const LEVEL_WARN = 30;
    const LEVEL_ERROR = 40;

    /**
     * log name
     *
     * @var string
     */
    protected static $logName;

    /**
     * log level
     *
     * @var int
     */
    protected static $logLevel;

    /**
     * Initialize
     *
     * @param string $logName log name, the default is 'app'
     * @param int $logLevel log level, the default is LEVEL_DEBUG
     * @return void
     */
    public static function init($logName = 'app', $logLevel = self::LEVEL_DEBUG)
    {
        self::$logName = $logName;
        self::$logLevel = $logLevel;
    }

    /**
     * Debug output
     *
     * @param string $msg message
     * @param mixed $obj object
     * @return void
     */
    public static function debug($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_DEBUG) {
            self::write('DEBUG', $msg, $obj);
        }
    }

    /**
     * Information output
     *
     * @param string $msg message
     * @param mixed $obj object
     * @return void
     */
    public static function info($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_INFO) {
            self::write('INFO', $msg, $obj);
        }
    }

    /**
     * Warning output
     *
     * @param string $msg message
     * @param mixed $obj object
     * @return void
     */
    public static function warn($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_WARN) {
            self::write('WARN', $msg, $obj);
        }
    }

    /**
     * Error output
     *
     * @param string $msg message
     * @param mixed $obj object
     * @return void
     */
    public static function error($msg, $obj = null)
    {
        if (self::$logLevel <= self::LEVEL_ERROR) {
            self::write('ERROR', $msg, $obj);
        }
    }

    /**
     * Write logs
     *
     * @param string $level level name
     * @param string $msg message
     * @param mixed $obj object
     * @return void
     */
    protected static function write($level, $msg, $obj)
    {
        $logfile = LOGS_DIR.'/'.self::$logName.'-'.date("Y-m-d").'.log';
        $log = self::buildLog($level, $msg, $obj);
        file_put_contents($logfile, $log, FILE_APPEND);
    }

    /**
     * Build logs
     *
     * @param string $level level name
     * @param string $msg message
     * @param mixed $obj object
     * @return string logs
     */
    protected static function buildLog($level, $msg, $obj)
    {
        $msg .= PHP_EOL;
        if ($obj !== null) {
            ob_start();
            var_dump($obj);
            $msg .= ob_get_contents();
            ob_end_clean();
        }
        $trace = debug_backtrace(false);
        $microtime = explode('.', (string)microtime(true));
        $caller = isset($trace[3]['class']) ? sprintf('%s%s%s', $trace[3]['class'], $trace[3]['type'], $trace[3]['function']) : '';
        $log = sprintf('[%s.%s][%s][%s][%s]%s', date('Y-m-d H:i:s'), $microtime[1], getmypid(), $level, $caller, $msg);
        //$log = sprintf('[%s.%s][%s][%s]%s', date('Y-m-d H:i:s', $tarray[0]), $tarray[1], getmypid(), $level, $msg);
        return $log;
    }
}
