<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

use Kore\Log;

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

        $lockfile = TMP_DIR.'/'.$this->commandName().'.lock';
        if (file_exists($lockfile) && filemtime($lockfile) + $this->lockTime() >= time()) {
            Log::warn('Process is running!');
            return;
        }
        touch($lockfile);

        Log::debug(sprintf('[START]%s', $this->command));
        try {
            $this->exec();
        } catch (\Exception $e) {
            $this->handleError($e);
        }
        Log::debug(sprintf('[END]%s', $this->command));

        unlink($lockfile);
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
     * @param \Exception $e errors
     * @return void
     */
    protected function handleError($e)
    {
        Log::error($e->getMessage());
    }

    /**
     * Get the command arguments
     *
     * If no key is specified, all command arguments are returned.
     * @param string|null $key command arguments key
     * @param mixed $default default value if there is no value specified in the key
     * @return string|array<string>|null command arguments
     */
    protected function getArg($key = null, $default = null)
    {
        if ($key === null) {
            return $this->args;
        }
        if (!isset($this->args[$key])) {
            return $default;
        }
        return $this->args[$key];
    }

    /**
     * Get the command options
     *
     * If no key is specified, all command options are returned.
     * @param string|null $key command options key
     * @param mixed $default default value if there is no value specified in the key
     * @return string|array<string>|null command options
     */
    protected function getOpt($key = null, $default = null)
    {
        if ($key === null) {
            return $this->opts;
        }
        if (!isset($this->opts[$key])) {
            return $default;
        }
        return $this->opts[$key];
    }
}
