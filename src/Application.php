<?php
namespace Kore;

class Application
{    
    /**
     * @var string
     */   
    private $basePath = '';
    /**
     * @var string
     */
    private $defaultController = 'index';

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
        if ($path === null) $path = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }
        $path = rawurldecode($path);
        $path = trim($path, '/');

        if (!empty($this->basePath) && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
    
        $parray = explode('/', $path);
    
        foreach ($parray as $i => $p) {
            $controller = implode('\\', array_slice($parray, 0, $i+1));
            if ($controller === '') $controller = $this->defaultController;
            $controller = CONTROLLERS_NS.$controller;
            if (class_exists($controller)) {
                $class = new $controller();
                $args = array_slice($parray, $i+1);
                break;
            }
    
            if ($i === count($parray)-1) {
                http_response_code(404);
                exit;
            }
        }
    
        $class->main($controller, $args);      
    }

    /**
     * @param array $argv
     * @return void
     */
    public function runCmd($argv)
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
            if ($key > 1 && isset($value)) {
                if (preg_match('/^--[a-zA-Z0-9]+=[a-zA-Z0-9]+$/', $value)) {
                    $params = explode('=', $value);
                    $name = str_replace('--', '', $params[0]);
                    $opts[$name] = $params[1];
                } else {
                    $args[] = $value;
                }
            }
        }

        $class->main($command, $args, $opts);
    }
}
