<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

/**
 * Config class
 *
 * Managing the configurations
 */
class Config
{
    /**
     * configurations
     *
     * An array of configuration values loaded from the configuration file.
     * @var array<mixed>
     */
    private static $config;

    /**
     * Creating the configurations
     *
     * Load the configurations from CONFIG_DIR/config-config-$env.php.
     * If the environment is not specified, load the configurations from CONFIG_DIR/config.php.
     * Environment-independent configurations are defined in CONFIG_DIR/config-common.php.
     * @param string|null $env environment
     * @return void
     * @throws \Exception thrown when the configuration file does not exist
     */
    public static function create($env = null)
    {
        if (empty(self::$config)) {
            $env = ($env === null) ? '' : '-'.$env;
            $configfile = CONFIG_DIR.'/config'.$env.'.php';
            if (!file_exists($configfile)) {
                throw new \Exception('Unable to find config file -> ' . $configfile);
            }
            self::$config = require $configfile;
            $commonConfig = require CONFIG_DIR.'/config-common.php';
            self::$config = array_merge(self::$config, $commonConfig);
        }
    }

    /**
     * Get the configurations
     *
     * If no key is specified, all configurations are returned.
     * If the key is passed dot-delimited, it is interpreted as an array path.
     * @param string|null $key configuration key
     * @param mixed $default default value if there is no value specified in the key
     * @return mixed configurations
     */
    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return self::$config;
        }
        $data = self::$config;
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (isset($data[$k])) {
                $data = $data[$k];
            } else {
                return $default;
            }
        }
        return $data;
    }
}
