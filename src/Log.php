<?php
namespace CoreLibrary;

class Log
{
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARN = 'WARN';
    const ERROR = 'ERROR';

    /**
     * @var string
     */
    private static $logName = 'app';

    /**
     * @param string $logName
     * @return void
     */
    public static function init($logName)
    {
        self::$logName = $logName;
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function debug($msg, $obj = null)
    {
        self::write(self::DEBUG, $msg, $obj);
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function info($msg, $obj = null)
    {
        self::write(self::INFO, $msg, $obj);
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function warn($msg, $obj = null)
    {
        self::write(self::WARN, $msg, $obj);
    }

    /**
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    public static function error($msg, $obj = null)
    {
        self::write(self::ERROR, $msg, $obj);
    }

    /**
     * @param string $logLevel
     * @param string $msg
     * @param mixed $obj
     * @return void
     */
    private static function write($logLevel, $msg, $obj)
    {
        $logfile = LOGS_DIR.self::$logName.'-'.date("Y-m-d").'.log';
        $msg .= PHP_EOL;
        if ($obj !== null) {
            ob_start();
            var_dump($obj);
            $msg .= ob_get_contents();
            ob_end_clean();
        }
        $tarray = explode('.', microtime(true));
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($trace[2]['class']) ? sprintf('%s%s%s', $trace[2]['class'], $trace[2]['type'], $trace[2]['function']) : '';
        $log = sprintf('[%s.%s][%s][%s][%s]%s', date('Y-m-d H:i:s', $tarray[0]), $tarray[1], getmypid(), $logLevel, $caller, $msg);
        //$log = sprintf('[%s.%s][%s][%s]%s', date('Y-m-d H:i:s', $tarray[0]), $tarray[1], getmypid(), $logLevel, $msg);
        file_put_contents($logfile, $log, FILE_APPEND);
    }
}
