<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

use Kore\Log;

/**
 * Controller class
 *
 */
abstract class Controller
{
    /**
     * controller namespace
     *
     * @var string
     */
    protected $controller;
    /**
     * path arguments
     *
     * @var array<string>
     */
    protected $args = [];

    /**
     * Action
     *
     * The action is implemented in subclasses.
     * @return void
     */
    abstract protected function action();

    /**
     * Main Processing
     *
     * @param string $controller controller namespace
     * @param array<string> $args path arguments
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
     * Get the the module name
     *
     * The default is 'app'.
     * If you need to customize, please override it with subclasses.
     * @return string module name
     */
    protected function moduleName()
    {
        return 'app';
    }

    /**
     * Get the the log level
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
     * Preprocessing of the action
     *
     * If you need to customize, please override it with subclasses.
     * @return void
     */
    protected function preaction()
    {
        // Override if necessary
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
        // Override if necessary
    }

    /**
     * Get the http method
     *
     * @return string http method
     */
    protected function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get the http header
     *
     * @param string $key header key
     * @param mixed $default default value if there is no value specified in the key
     * @return string|null http header
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
     * Get the query parameters
     *
     * If no key is specified, all query parameters are returned.
     * @param string|null $key query parameters key
     * @param mixed $default default value if there is no value specified in the key
     * @return string|array<string>|null query parameters
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
     * Get the post parameters
     *
     * If no key is specified, all post parameters are returned.
     * @param string|null $key post parameters key
     * @param mixed $default default value if there is no value specified in the key
     * @return string|array<string>|null post parameters
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
     * Get the body data
     *
     * @return string body data
     */
    protected function getBody()
    {
        $body = file_get_contents('php://input');
        $body = $body !== false ? $body : '';
        return $body;
    }

    /**
     * Get the body data in json format
     *
     * @return array<mixed> body data
     */
    protected function getJsonBody()
    {
        $body = json_decode($this->getBody(), true);
        return $body;
    }

    /**
     * Get the cookie parameters
     *
     * If no key is specified, all cookie parameters are returned.
     * @param string|null $key cookie parameters key
     * @param mixed $default default value if there is no value specified in the key
     * @return string|array<string>|null cookie parameters
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
     * Get the path arguments
     *
     * If no key is specified, all path arguments are returned.
     * @param string|null $key path arguments key
     * @param mixed $default default value if there is no value specified in the key
     * @return string|array<string>|null path arguments
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
     * Respond in view format
     *
     * @param string $path view file path
     * @param array<mixed> $data response data
     * @param int $responseCode http status code, the default is 200
     * @return void
     */
    protected function respondView($path, $data=[], $responseCode = 200)
    {
        http_response_code($responseCode);
        require(VIEWS_DIR.'/'.$path.'.php');
    }

    /**
     * Extract the view
     *
     * @param string $path view file path
     * @param array<mixed> $data response data
     * @return string|false view
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
     * Respond in json format
     *
     * @param array<mixed> $data response data
     * @param int $responseCode http status code, the default is 200
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
     * Redirect
     *
     * @param string $url redirect url
     * @param int $responseCode http status code, the default is 302
     * @return void
     */
    protected function redirect($url, $responseCode = 302)
    {
        header("Location: $url", true, $responseCode);
    }
}
