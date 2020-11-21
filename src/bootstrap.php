<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

/**
 * Initialize the application
 *
 * @param string $app_dir Application Directory
 * @param string $app_ns Application NameSpace
 * @return void
 */
function bootstrap($app_dir, $app_ns)
{
    define('APP_DIR', $app_dir);
    define('APP_NS', $app_ns);
    
    // define directory
    define('CONFIG_DIR', APP_DIR.'/config');
    define('CONTROLLERS_DIR', APP_DIR.'/controllers');
    define('COMMANDS_DIR', APP_DIR.'/commands');
    define('VIEWS_DIR', APP_DIR.'/views');
    define('LIBS_DIR', APP_DIR.'/libs');
    define('TMP_DIR', dirname(APP_DIR) .'/tmp');
    define('LOGS_DIR', TMP_DIR.'/logs');

    // define namespace
    define('CONTROLLERS_NS', APP_NS.'\\controllers');
    define('COMMANDS_NS', APP_NS.'\\commands');
}
