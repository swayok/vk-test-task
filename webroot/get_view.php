<?php

$srcDir = __DIR__ . '/../src/';

require_once $srcDir . 'configs/constants.php';
require_once $srcDir . 'lib/antihack.view.php';

$viewFilePath = $srcDir . 'views/' . $_GET['view'] . '.php';
if (empty($_GET['view']) || !file_exists($viewFilePath)) {
    \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
    exit;
}

require_once $srcDir . 'configs/dictionary.php';

include $viewFilePath;

