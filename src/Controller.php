<?php
namespace Kore;

use Kore\Log;

abstract class Controller
{
    /**
     * @var string
     */
    protected $controller;
    /**
     * @var array<string>
     */
    protected $args = [];

    /**
     * @return void
     */
    abstract protected function action();

    /**
     * @param string $controller
     * @param array<string> $args
     * @return void
     */
    public function main($controller, $args)
    {
        $this->controller = $controller;
        $this->args = $args;
        Log::init($this->moduleName());

        Log::info(sprintf('[START][%s]%s', $this->getMethod(), $this->controller));
        try {
            $this->preaction();
            $this->action();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->handleError($e);
        }
        Log::info(sprintf('[END][%s]%s', $this->getMethod(), $this->controller));
    }

    /**
     * @return string
     */
    protected function moduleName()
    {
        return 'app';
    }

    /**
     * @return void
     */
    protected function preaction()
    {
        // Override if necessary
    }

    /**
     * @param \Exception $e
     * @return void
     */
    protected function handleError($e)
    {
        // Override if necessary
    }

    // Request Method
    //

    /**
     * @return string
     */
    protected function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param string $key
     * @return string|null
     */
    protected function getHeader($key)
    {
        $headerName = 'HTTP_' . str_replace('-', '_', strtoupper($key));
        if (!isset($_SERVER[$headerName])) {
            return null;
        }
        return $_SERVER[$headerName];
    }

    /**
     * @param string $key
     * @return string|null
     */
    protected function getQuery($key)
    {
        if (!isset($_GET[$key])) {
            return null;
        }
        return $_GET[$key];
    }

    /**
     * @param string $key
     * @return string|null
     */
    protected function getPost($key)
    {
        if (!isset($_POST[$key])) {
            return null;
        }
        return $_POST[$key];
    }

    /**
     * @return object|false
     */
    protected function getInput()
    {
        $rawInput = file_get_contents('php://input');
        if (!$rawInput) {
            return false;
        }
        $input = json_decode($rawInput, true);
        return $input;
    }

    /**
     * @param string $key
     * @return string|null
     */
    protected function getCookie($key)
    {
        if (!isset($_COOKIE[$key])) {
            return null;
        }
        return $_COOKIE[$key];
    }
 
    /**
     * @param string $key
     * @return string|null
     */
    protected function getArg($key)
    {
        if (!isset($this->args[$key])) {
            return null;
        }
        return $this->args[$key];
    }

    // Response Method
    //

    /**
     * @param string $path
     * @param array<mixed> $data
     * @return void
     */
    protected function respondView($path, $data=[])
    {
        require(VIEWS_DIR.'/'.$path.'.php');
    }

    /**
     * @param string $path
     * @param array<mixed> $data
     * @return string|false
     */
    protected function extractView($path, $data=[])
    {
        ob_start();
        $this->respondView($path, $data);
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    /**
     * @param array<mixed> $data
     * @return void
     */
    protected function respondJson($data=[])
    {
        $json = json_encode($data);
        header('Content-Type: application/json');
        echo $json;
    }
}
