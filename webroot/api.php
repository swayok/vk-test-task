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
    $response = json_decode($exc->getMessage(), true);
    if ($response === false) {
        $response = array('message' => $exc->getMessage());
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;