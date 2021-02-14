<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

if (!function_exists('url_add_query')) {
    /**
     * Add query parameters to URL
     * @param string $url URL
     * @param array<mixed> $params Query parameters
     * @return string URL with query parameters
     */
    function url_add_query($url, $params)
    {
        return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
    }
}

if (!function_exists('array_get_recursive')) {
    /**
     * Get array values recursively
     * 
     * If the key is passed dot-delimited, it is interpreted as an array path.
     * @param array<mixed> $array array
     * @param string $key key
     * @param mixed $default default value
     * @return mixed value
     */
    function array_get_recursive($array, $key, $default = null)
    {
        $value = $array;
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        return $value;
    }
}
