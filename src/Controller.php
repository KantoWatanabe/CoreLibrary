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
     * @var array
     */
    protected $args = [];

    /**
     * @return void
     */
    abstract protected function action();

    /**
     * @param string $controller
     * @param array $args
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
     * @return string
     */
    protected function getQuery($key)
    {
        if (!isset($_GET[$key])) return null;
        return $_GET[$key];
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getPost($key)
    {
        if (!isset($_POST[$key])) return null;
        return $_POST[$key];
    }

    /**
     * @return object
     */
    protected function getInput()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getCookie($key)
    {
        if (!isset($_COOKIE[$key])) return null;
        return $_COOKIE[$key];
    }
 
    /**
     * @param string $key
     * @return string
     */
    protected function getArg($key)
    {
        if (!isset($this->args[$key])) return null;
        return $this->args[$key];
    }

    // Response Method
    //

    /**
     * @param string $path
     * @return array $data
     * @return void
     */
    protected function respondView($path, $data=[])
    {
        require(VIEWS_DIR.$path.'.php');
    }

    /**
     * @param string $path
     * @return array $data
     * @return string
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
     * @return array $data
     * @return void
     */
    protected function respondJson($data=[])
    {
        $json = json_encode($data);
        header('Content-Type: application/json');
        echo $json;
    }
}
