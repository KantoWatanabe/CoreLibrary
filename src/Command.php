<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

use Kore\Log;
use Exception;

/**
 * Command class
 *
 */
abstract class Command
{
    /**
     * command namespace
     *
     * @var string
     */
    protected $command;
    /**
     * command arguments
     *
     * @var array<string>
     */
    protected $args = array();
    /**
     * command options
     *
     * @var array<string>
     */
    protected $opts = array();
    /**
     * LockManager
     *
     * @var \Kore\LockManager
     */
    protected $lockManager;

    /**
     * Processing Execution
     *
     * The process is implemented in subclasses.
     * @return void
     */
    abstract protected function exec();
    
    /**
     * Main Processing
     *
     * @param string $command command namespace
     * @param array<string> $args command arguments
     * @param array<string> $opts command options
     * @return void
     */
    public function main($command, $args, $opts)
    {
        $this->command = $command;
        $this->args = $args;
        $this->opts = $opts;
        Log::init($this->commandName(), $this->logLevel());

        $this->lockManager = new LockManager($this->commandName(), $this->lockTime());
        if ($this->lockManager->isLock()) {
            Log::warn('Process is running!');
            return;
        }
        $this->lockManager->lock();

        Log::debug(sprintf('[START]%s', $this->command));
        try {
            $this->exec();
        } catch (Exception $e) {
            $this->lockManager->unlock();
            $this->handleError($e);
        }
        Log::debug(sprintf('[END]%s', $this->command));

        $this->lockManager->unlock();
    }

    /**
     * Get the command name
     *
     * The default is the end of the command namespace.
     * If you need to customize, please override it with subclasses.
     * @return string command name
     */
    protected function commandName()
    {
        $namespace = explode('\\', $this->command);
        return $namespace[count($namespace) - 1];
    }

    /**
     * Get the log level
     *
     * The default is Log::LEVEL_DEBUG.
     * If you need to customize, please override it with subclasses.
     * @return int log level
     * @see \Kore\Log
     */
    protected function logLevel()
    {
        return Log::LEVEL_DEBUG;
    }

    /**
     * Get the lock time
     *
     * The default is 24 hours.
     * If you need to customize, please override it with subclasses.
     * @return int lock time
     */
    protected function lockTime()
    {
        return 60 * 60 * 24;
    }

    /**
     * Handling Errors
     *
     * If you need to customize the handling of errors, please override it with subclasses.
     * @param Exception $e errors
     * @return void
     * @throws Exception
     */
    protected function handleError($e)
    {
        Log::error($e);
        throw $e;
    }

    /**
     * Handling Shutdown
     *
     * If you need to customize the handling of shutdown, please override it with subclasses.
     * @return void
     * @codeCoverageIgnore
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error) {
            $errorFile = $error['file'];
            $errorLine = $error['line'];
            $errorMessage = $error['message'];
            $errorType = $error['type'];

            if ($errorType === E_ERROR) {
                $this->lockManager->unlock();
                $this->handleError(new Exception("$errorMessage $errorFile:$errorLine"));
            }
        }
    }

    /**
     * Get the command arguments
     *
     * If no key is specified, all command arguments are returned.
     * @param string|null $key command argument key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed command arguments
     */
    protected function getArg($key = null, $default = null)
    {
        return $this->getFromArray($this->args, $key, $default);
    }

    /**
     * Get the command options
     *
     * If no key is specified, all command options are returned.
     * @param string|null $key command option key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed command options
     */
    protected function getOpt($key = null, $default = null)
    {
        return $this->getFromArray($this->opts, $key, $default);
    }

    /**
     * Get from array
     *
     * If no key is specified, array is returned.
     * @param array<mixed> $array array
     * @param string|null $key path key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed value
     */
    protected function getFromArray($array, $key = null, $default = null)
    {
        if ($key === null) {
            return $array;
        }
        if (!isset($array[$key])) {
            return $default;
        }
        return $array[$key];
    }
}

/**
 * Managing Command Lock
 *
 */
class LockManager
{
    /** @var string process name */
    protected $pname;
    /** @var int lock time */
    protected $lockTime;
    /** @var string lock file */
    protected $lockFile;

    /**
     * __construct
     * @param string $pname process name
     * @param int $lockTime lock time
     * @return void
     */
    public function __construct($pname, $lockTime)
    {
        $this->pname = $pname;
        $this->lockTime = $lockTime;
    }

    /**
     * Lock
     * @return void
     */
    public function lock()
    {
        touch($this->lockFile());
    }

    /**
     * Unlock
     * @return void
     */
    public function unlock()
    {
        if (file_exists($this->lockFile())) {
            unlink($this->lockFile());
        }
    }

    /**
     * Locked or not
     * @return boolean locked or not
     */
    public function isLock()
    {
        return file_exists($this->lockFile()) && filemtime($this->lockFile()) + $this->lockTime >= time();
    }

    /**
     * Get lock file
     * @return string lock file
     */
    protected function lockFile()
    {
        if (!$this->lockFile) {
            $this->lockFile = TMP_DIR.'/'.$this->pname.'.lock';
        }
        return $this->lockFile;
    }
}
