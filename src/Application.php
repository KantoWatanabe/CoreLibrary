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
     * @param string $basePath ex. 'mybasepath/'
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
     * @param string|null $path
     * @return void
     */
    public function run($path = null)
    {
        if ($path === null) {
            $path = $_SERVER['REQUEST_URI'];
        }

        list($class, $controller, $args) = $this->parseController($path);

        if ($class === null) {
            http_response_code(404);
            exit;
        }
    
        $class->main($controller, $args);
    }

    /**
     * @param string $path
     * @return array<mixed>
     */
    protected function parseController($path)
    {
        if (false !== $pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }
        $path = rawurldecode($path);
        $path = trim($path, '/');

        if (!empty($this->basePath) && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
    
        $parray = explode('/', $path);
    
        $class = null;
        $controller = $this->defaultController;
        $args = [];
        foreach ($parray as $i => $p) {
            $tmpController = implode('\\', array_slice($parray, 0, $i+1));
            if ($tmpController !== '') {
                $controller = $tmpController;
            }
            $controller = CONTROLLERS_NS.$controller;
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
        list($class, $command, $args, $opts) = $this->parseCommand($argv);

        $class->main($command, $args, $opts);
    }

    /**
     * @param array<string> $argv
     * @return array<mixed>
     */
    protected function parseCommand($argv)
    {
        if (!isset($argv[1])) {
            throw new \Exception('Unable to find command name');
        }
        
        $command = COMMANDS_NS.str_replace('/', '\\', $argv[1]);
        if (!class_exists($command)) {
            throw new \Exception('Unable to load command class ->' . $command);
        }
        
        $class = new $command();

        $args = [];
        $opts = [];
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
