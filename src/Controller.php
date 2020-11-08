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
        Log::init($this->moduleName(), $this->logLevel());

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
     * @return int
     */
    protected function logLevel()
    {
        return Log::LEVEL_DEBUG;
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
     * @param mixed $default
     * @return string|null
     */
    protected function getHeader($key, $default = null)
    {
        $headerName = 'HTTP_' . str_replace('-', '_', strtoupper($key));
        if (!isset($_SERVER[$headerName])) {
            return $default;
        }
        return $_SERVER[$headerName];
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return string|array<string>
     */
    protected function getQuery($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        if (!isset($_GET[$key])) {
            return $default;
        }
        return $_GET[$key];
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return string|array<string>
     */
    protected function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        if (!isset($_POST[$key])) {
            return $default;
        }
        return $_POST[$key];
    }

    /**
     * @return string
     */
    protected function getBody()
    {
        $body = file_get_contents('php://input');
        $body = $body !== false ? $body : '';
        return $body;
    }

    /**
     * @return array<mixed>
     */
    protected function getJsonBody()
    {
        $body = json_decode($this->getBody(), true);
        return $body;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return string|array<string>
     */
    protected function getCookie($key = null, $default = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }
        if (!isset($_COOKIE[$key])) {
            return $default;
        }
        return $_COOKIE[$key];
    }
 
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

    // Response Method
    //

    /**
     * @param string $path
     * @param array<mixed> $data
     * @param int $responseCode
     * @return void
     */
    protected function respondView($path, $data=[], $responseCode = 200)
    {
        http_response_code($responseCode);
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
     * @param int $responseCode
     * @return void
     */
    protected function respondJson($data=[], $responseCode = 200)
    {
        $json = json_encode($data);
        http_response_code($responseCode);
        header('Content-Type: application/json');
        echo $json;
    }

    /**
     * @param string $url
     * @param int $responseCode
     * @return void
     */
    protected function redirect($url, $responseCode = 302)
    {
        header("Location: $url", true, $responseCode);
    }
}
