<?php
namespace Kore;

use Kore\Log;

class HttpClient
{
    /**
     * @param string $url
     * @param array<mixed> $params
     * @param array<mixed> $headers
     * @param string|null $userpwd
     * @return mixed
     */
    public function get($url, $params = [], $headers = [], $userpwd = null)
    {
        return $this->communicate('GET', $url, $params, $headers, $userpwd);
    }

    /**
     * @param string $url
     * @param array<mixed> $params
     * @param array<mixed> $headers
     * @param string|null $userpwd
     * @return mixed
     */
    public function post($url, $params = [], $headers = [], $userpwd = null)
    {
        return $this->communicate('POST', $url, $params, $headers, $userpwd);
    }

    /**
     * @param string $url
     * @param array<mixed> $params
     * @param array<mixed> $headers
     * @param string|null $userpwd
     * @return mixed
     */
    public function put($url, $params = [], $headers = [], $userpwd = null)
    {
        return $this->communicate('PUT', $url, $params, $headers, $userpwd);
    }

    /**
     * @param string $url
     * @param array<mixed> $params
     * @param array<mixed> $headers
     * @param string|null $userpwd
     * @return mixed
     */
    public function patch($url, $params = [], $headers = [], $userpwd = null)
    {
        return $this->communicate('PATCH', $url, $params, $headers, $userpwd);
    }

    /**
     * @param string $url
     * @param array<mixed> $params
     * @param array<mixed> $headers
     * @param string|null $userpwd
     * @return mixed
     */
    public function delete($url, $params = [], $headers = [], $userpwd = null)
    {
        return $this->communicate('DELETE', $url, $params, $headers, $userpwd);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array<mixed> $params
     * @param array<mixed> $headers
     * @param string|null $userpwd
     * @return HttpResponse|false
     */
    protected function communicate($method, $url, $params = [], $headers = [], $userpwd = null)
    {
        $headers = $this->buildHeader($headers);
        
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if ($method === 'GET') {
            curl_setopt($curl, CURLOPT_URL, $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($params));
        } elseif ($method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
            curl_setopt($curl, CURLOPT_URL, $url);
            if (count(preg_grep("/^Content-Type: application\/json/i", $headers)) > 0) {
                $data = json_encode($params);
            } else {
                $data = http_build_query($params);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        if ($userpwd !== null) {
            curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
        }

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $total_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);

        curl_close($curl);

        Log::debug(sprintf('[%s][%s][%ssec]', $url, $http_code, $total_time));
        if (!$response) {
            Log::info('Acquisition failed');
            return false;
        }
        /** @phpstan-ignore-next-line */
        $header = substr($response, 0, $header_size);
        /** @phpstan-ignore-next-line */
        $body = substr($response, $header_size);
        return new HttpResponse($http_code, $header, $body);
    }

    /**
     * @param array<mixed> $headers
     * @return array<string>
     */
    protected function buildHeader($headers)
    {
        $h = array();
        foreach ($headers as $key => $value) {
            if (is_string($key)) {
                $h[] = "$key: $value";
            } else {
                $h[] = $value;
            }
        }
        return $h;
    }
}

class HttpResponse
{
    /**
     * @param int $httpCode
     * @param string $header
     * @param string $body
     * @return void
     */
    public function __construct($httpCode, $header, $body)
    {
        $this->httpCode = $httpCode;
        $this->header = $header;
        $this->body = $body;
    }

    /**
     * @var int
     */
    private $httpCode;
    /**
     * @var string
     */
    private $header;
    /**
     * @var string
     */
    private $body;

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getHeaderLine($key)
    {
        preg_match("/$key: ([-a-zA-Z]*)/i", $this->getHeader(), $matches);
        if (!isset($matches[1])) {
            return null;
        }
        return $matches[1];
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * @return array<mixed>
     */
    public function getJsonBody()
    {
        return json_decode($this->getBody(), true);
    }
}
