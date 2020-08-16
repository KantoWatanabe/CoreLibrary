<?php

// for test
date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('UTF-8');

define('ROOT_DIR', __DIR__.'/');
define('APP_DIR', ROOT_DIR.'mock/');
define('APP_NS', 'mock\\');

require(__DIR__.'/../src/bootstrap.php');

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
