<?php
namespace Kore;

use Kore\Log;

abstract class Command
{
    /**
     * @var string
     */
    protected $command;
    /**
     * @var array<string>
     */
    protected $args = [];
    /**
     * @var array<string>
     */
    protected $opts = [];

    /**
     * @return void
     */
    abstract protected function exec();
    
    /**
     * @param string $command
     * @param array<string> $args
     * @param array<string> $opts
     * @return void
     */
    public function main($command, $args, $opts)
    {
        $this->command = $command;
        $this->args = $args;
        $this->opts = $opts;
        Log::init($this->commandName(), $this->logLevel());

        $lockfile = TMP_DIR.'/'.$this->commandName().'.lock';
        if (file_exists($lockfile)) {
            Log::warn('Process is running!');
            return;
        }
        touch($lockfile);

        Log::info(sprintf('[START]%s', $this->command));
        try {
            $this->exec();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->handleError($e);
        }
        Log::info(sprintf('[END]%s', $this->command));

        unlink($lockfile);
    }

    /**
     * @return string
     */
    protected function commandName()
    {
        $namespace = explode('\\', $this->command);
        /** @phpstan-ignore-next-line */
        return end($namespace);
    }

    /**
     * @return int
     */
    protected function logLevel()
    {
        return Log::LEVEL_DEBUG;
    }

    /**
     * @param \Exception $e
     * @return void
     */
    protected function handleError($e)
    {
        // Override if necessary
    }

    // Command Parameter
    //
    
    /**
     * @param string $key
     * @param mixed $default
     * @return string|array<string>
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
     * @param string $key
     * @param mixed $default
     * @return string|array<string>
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
