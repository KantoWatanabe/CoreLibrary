<?php
namespace Kore;

class Application
{
    /**
     * @var string
     */
    protected $basePath = '';
    /**
     * @var string
     */
    protected $defaultController = 'index';
    /**
     * @var callable
     */
    protected $notFound;

    /**
     * @param string $basePath ex. 'mybasepath'
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param string $defaultController
     * @return void
     */
    public function setDefaultController($defaultController)
    {
        $this->defaultController = $defaultController;
    }

    /**
     * @param callable $notFound
     * @return void
     */
    public function setNotFound($notFound)
    {
        if (is_callable($notFound)) {
            $this->notFound = $notFound;
        }
    }
    
    /**
     * @param string|null $path
     * @return void
     */
    public function run($path = null)
    {
        if ($path === null) {
            $path = $_SERVER['REQUEST_URI'];
        }
        if (!empty($this->basePath) && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
        if (false !== $pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }
        $path = rawurldecode($path);
        $path = trim($path, '/');

        list($class, $controller, $args) = $this->parseController($path);

        if ($class === null) {
            if (is_callable($this->notFound)) {
                call_user_func($this->notFound);
            }
            http_response_code(404);
            return;
        }
    
        $class->main($controller, $args);
    }

    /**
     * @param string $path
     * @return array<mixed>
     */
    protected function parseController($path)
    {
        $parray = explode('/', $path);
    
        $class = null;
        $controller = $this->defaultController;
        $args = [];
        foreach ($parray as $i => $p) {
            $tmpController = implode('\\', array_slice($parray, 0, $i+1));
            if ($tmpController !== '') {
                $controller = $tmpController;
            }
            $controller = CONTROLLERS_NS.'\\'.$controller;
            if (class_exists($controller)) {
                $class = new $controller();
                $args = array_slice($parray, $i+1);
                break;
            }
        }
        return [$class, $controller, $args];
    }

    /**
     * @param array<string> $argv
     * @return void
     */
    public function runCmd($argv)
    {
        if (!isset($argv[1])) {
            throw new \Exception('Unable to find command name');
        }

        list($class, $command, $args, $opts) = $this->parseCommand($argv);

        if ($class === null) {
            throw new \Exception('Unable to load command class ->' . $command);
        }

        $class->main($command, $args, $opts);
    }

    /**
     * @param array<string> $argv
     * @return array<mixed>
     */
    protected function parseCommand($argv)
    {
        $class = null;
        $command = COMMANDS_NS.'\\'.str_replace('/', '\\', $argv[1]);
        $args = [];
        $opts = [];

        if (!class_exists($command)) {
            return [$class, $command, $args, $opts];
        }

        $class = new $command();
        foreach ($argv as $key => $value) {
            if ($key > 1) {
                if (preg_match('/^--[a-zA-Z0-9]+=[a-zA-Z0-9]+$/', $value)) {
                    $params = explode('=', $value);
                    $name = str_replace('--', '', $params[0]);
                    $opts[$name] = $params[1];
                } else {
                    $args[] = $value;
                }
            }
        }
        return [$class, $command, $args, $opts];
    }
}
