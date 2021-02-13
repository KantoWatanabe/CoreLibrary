<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

/**
 * Application class
 *
 * Running web and cli applications.
 */
class Application
{
    /**
     * Base path
     *
     * Excluded when parsing the controller namespace.
     * Used in running web applications.
     * @var string
     */
    protected $basePath = '';
    /**
     * Default controller
     *
     * Called if the controller is not specified.
     * If unspecified, the default is 'index'.
     * Used in running web applications.
     * @var string
     */
    protected $defaultController = 'index';
    /**
     * Not found handler
     *
     * Called when the controller is not found.
     * Used in running web applications.
     * @var callable
     */
    protected $notFound;

    /**
     * Set the base path
     *
     * @param string $basePath bath path ex. 'mybasepath'
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Set the default controller
     *
     * @param string $defaultController default controller
     * @return void
     */
    public function setDefaultController($defaultController)
    {
        $this->defaultController = $defaultController;
    }

    /**
     * Set the not found handler
     *
     * @param callable $notFound not found handler
     * @return void
     */
    public function setNotFound($notFound)
    {
        if (is_callable($notFound)) {
            $this->notFound = $notFound;
        }
    }
    
    /**
     * Running web applications
     *
     * @param string|null $path request path
     * @return void
     */
    public function run($path = null)
    {
        if ($path === null) {
            $path = $_SERVER['REQUEST_URI'];
        }
        if (false !== $pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }
        $path = rawurldecode($path);
        $path = trim($path, '/');
        if (!empty($this->basePath) && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
            $path = trim($path, '/');
        }

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
     * Parse the controller namespace from the request path
     *
     * @param string $path request path
     * @return array<mixed> controller information
     *                      array(
     *                          controller class instance,
     *                          controller namespace,
     *                          path arguments
     *                      )
     */
    protected function parseController($path)
    {
        $parray = explode('/', $path);
    
        $class = null;
        $controller = $this->defaultController;
        $args = array();
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
        return array($class, $controller, $args);
    }

    /**
     * Running cli applications
     *
     * @param array<string> $argv command line argument
     * @return void
     * @throws \Exception thrown when the command class is not found.
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
     * Parse the command namespace from the command line argument
     *
     * @param array<string> $argv command line argument
     * @return array<mixed> command information
     *                      array(
     *                          commadn class instance,
     *                          command namespace,
     *                          arguments,
     *                          options
     *                      )
     */
    protected function parseCommand($argv)
    {
        $class = null;
        $command = COMMANDS_NS.'\\'.str_replace('/', '\\', $argv[1]);
        $args = array();
        $opts = array();

        if (!class_exists($command)) {
            return array($class, $command, $args, $opts);
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
        return array($class, $command, $args, $opts);
    }
}
