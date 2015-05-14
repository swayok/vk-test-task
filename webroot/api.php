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

$response = \Api\Controller\runAction($action);

echo json_encode($response);
exit;