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
    protected $args = array();
    /**
     * request body
     *
     * @var string
     */
    protected $body;

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

        Log::debug(sprintf('[START]%s', $this->controller));
        try {
            $this->preaction();
            $this->action();
        } catch (\Exception $e) {
            $this->handleError($e);
        }
        Log::debug(sprintf('[END]%s', $this->controller));
    }

    /**
     * Get the module name
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
     * Get the log level
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
     * @throws \Exception
     */
    protected function handleError($e)
    {
        Log::error($e);
        throw $e;
    }

    /**
     * Get the http method
     *
     * @return mixed http method
     */
    protected function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Get the User Agent
     *
     * @return mixed User Agent
     */
    protected function getUserAgent()
    {
        return $this->getServer('HTTP_USER_AGENT');
    }

    /**
     * Get the http header
     *
     * @param string $key header key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed http header
     */
    protected function getHeader($key, $default = null)
    {
        $headerName = 'HTTP_' . str_replace('-', '_', strtoupper($key));
        return $this->getServer($headerName, $default);
    }

    /**
     * Get the query parameters
     *
     * If no key is specified, all query parameters are returned.
     * @param string|null $key query parameter key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed query parameters
     */
    protected function getQuery($key = null, $default = null)
    {
        return $this->getFromArray($_GET, $key, $default);
    }

    /**
     * Get the post parameters
     *
     * If no key is specified, all post parameters are returned.
     * @param string|null $key post parameter key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed post parameters
     */
    protected function getPost($key = null, $default = null)
    {
        return $this->getFromArray($_POST, $key, $default);
    }

    /**
     * Get the body data
     *
     * @return string body data
     */
    protected function getBody()
    {
        if (!isset($this->body)) {
            $body = file_get_contents('php://input');
            $this->body = $body !== false ? $body : '';
        }
        return $this->body;
    }

    /**
     * Get the body data in json format
     *
     * @return array<mixed> body data
     */
    protected function getJsonBody()
    {
        return json_decode($this->getBody(), true);
    }

    /**
     * Get the cookie parameters
     *
     * If no key is specified, all cookie parameters are returned.
     * @param string|null $key cookie parameter key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed cookie parameters
     */
    protected function getCookie($key = null, $default = null)
    {
        return $this->getFromArray($_COOKIE, $key, $default);
    }

    /**
     * Get the server parameters
     *
     * If no key is specified, all server parameters are returned.
     * @param string|null $key server parameter key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed server parameters
     */
    protected function getServer($key = null, $default = null)
    {
        return $this->getFromArray($_SERVER, $key, $default);
    }
 
    /**
     * Get the path arguments
     *
     * If no key is specified, all path arguments are returned.
     * @param string|null $key path argument key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed path arguments
     */
    protected function getArg($key = null, $default = null)
    {
        return $this->getFromArray($this->args, $key, $default);
    }

    /**
     * Get from array
     *
     * If no key is specified, array is returned.
     * @param array<mixed> $array array
     * @param string|null $key path key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed value
     */
    protected function getFromArray($array, $key = null, $default = null)
    {
        if ($key === null) {
            return $array;
        }
        if (!isset($array[$key])) {
            return $default;
        }
        return $array[$key];
    }

    /**
     * Respond in view format
     *
     * @param string $path view file path
     * @param array<mixed> $data response data
     * @param int $responseCode http status code, the default is 200
     * @return void
     */
    protected function respondView($path, $data=array(), $responseCode = 200)
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
    protected function extractView($path, $data=array())
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
    protected function respondJson($data=array(), $responseCode = 200)
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
