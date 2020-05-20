<?php

// 本当はexitしたいが、testでcomposerのautoload filesした際に終了してしまうためreturnする
if (!defined('APP_DIR') or !defined('APP_NS')) return;

// define directory
define('CONFIG_DIR', APP_DIR.'config/');
define('CONTROLLERS_DIR', APP_DIR.'controllers/');
define('COMMANDS_DIR', APP_DIR.'commands/');
define('VIEWS_DIR', APP_DIR.'views/');
define('TMP_DIR', APP_DIR.'tmp/');
define('LOGS_DIR', TMP_DIR.'logs/');
define('BIN_DIR', APP_DIR.'bin/');

// define namespace
define('CONTROLLERS_NS', APP_NS.'controllers\\');
define('COMMANDS_NS', APP_NS.'commands\\');
