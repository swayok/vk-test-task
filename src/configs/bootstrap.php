<?php

define('DEBUG', true);
error_reporting(E_ALL);
ini_set('track_errors', true);
ini_set('html_errors', true);
require_once __DIR__ . '/../lib/error.reporter.php';
ini_set('display_errors', false);
ini_set('display_startup_errors', true);

date_default_timezone_set('Europe/Moscow');

require_once 'constants.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../lib/antihack.php';
require_once __DIR__ . '/../lib/request.php';
require_once __DIR__ . '/../lib/storage.php';
require_once __DIR__ . '/../lib/debug.php';
require_once 'dictionary.php';
