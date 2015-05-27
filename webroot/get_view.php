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

if (!headers_sent()) {
    header('Content-Type: text/plain');
    if (ENVIRONMENT !== 'dev') {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time() - 1) . " GMT");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + VIEWS_HTTP_CACHE_DURATION) . " GMT");
        header("Pragma: cache");
        header('Cache-Control: public,max-age=' . VIEWS_HTTP_CACHE_DURATION);
        header('Vary: User-Agent');
        header('Etag: ' . sha1($viewFilePath));
    }
}

include $viewFilePath;
exit;