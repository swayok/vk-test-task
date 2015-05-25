<?php

$srcDir = __DIR__ . '/../src/';

require_once $srcDir . 'configs/bootstrap.php';
require_once $srcDir . 'configs/databases.php';

if (empty($_GET['action'])) {
    Utils\setHttpCode(Utils\HTTP_CODE_NOT_FOUND);
    exit;
}

require_once $srcDir . 'api/api.controller.php';

$action = strtolower($_GET['action']);
try {
    $response = \Api\Controller\runAction($action);
} catch (Exception $exc) {
    \Utils\setHttpCode($exc->getCode() >= 400 ? $exc->getCode() : 500);
    $message = $exc->getMessage();
    if ($message[0] == '{') {
        $response = json_decode($message, true);
    }
    if (empty($response)) {
        $response = array(
            '_message' => $exc->getMessage(),
            '_exception_info' => array('file' => $exc->getFile(), 'line' => $exc->getLine())
        );
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;