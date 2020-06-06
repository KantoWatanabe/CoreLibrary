<?php

// for test
date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('UTF-8');

define('ROOT_DIR', __DIR__.'/');
define('APP_DIR', ROOT_DIR.'mock/');
define('APP_NS', 'mock\\');

// define directory
define('CONFIG_DIR', APP_DIR.'config/');
define('CONTROLLERS_DIR', APP_DIR.'controllers/');
define('COMMANDS_DIR', APP_DIR.'commands/');
define('VIEWS_DIR', APP_DIR.'views/');
define('LIBS_DIR', APP_DIR.'libs/');
define('TMP_DIR', APP_DIR.'tmp/');
define('LOGS_DIR', TMP_DIR.'logs/');

// define namespace
define('CONTROLLERS_NS', APP_NS.'controllers\\');
define('COMMANDS_NS', APP_NS.'commands\\');

// AutoLoad
spl_autoload_register(function ($class) {

    $prefix = APP_NS;

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    $file = APP_DIR.str_replace('\\', '/', $relative_class).'.php';

    if (file_exists($file)) {
        require $file;
    }
});