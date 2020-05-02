<?php
namespace CoreLibray;

use CoreLibray\Log;

abstract class Command
{
    /**
     * @var string
     */ 
    protected $command;
    /**
     * @var array
     */
    protected $args = [];
    /**
     * @var array
     */
    protected $opts = [];

    /**
     * @return void
     */
    abstract protected function exec();
    
    /**
     * @var string $command
     * @var array $args
     * @var array $opts
     * @return void
     */
    public function main($command, $args, $opts)
    {
        $this->command = $command;
        $this->args = $args;
        $this->opts = $opts;
        Log::init($this->commandName());

        $lockfile = BIN_DIR.$this->commandName().'.lock';
        if (file_exists($lockfile)) {
            Log::error('Process is running!');
            exit(1);
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
        return lcfirst(end($namespace));
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
     * @return string
     */
    protected function getArg($key)
    {
        if (!isset($this->args[$key])) return null;
        return $this->args[$key];
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getOpt($key)
    {
        if (!isset($this->opts[$key])) return null;
        return $this->opts[$key];    
    }
}
