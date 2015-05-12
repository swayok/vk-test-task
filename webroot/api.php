<?php

$srcDir = __DIR__ . '/../src/';

require_once $srcDir . 'configs/bootstrap.php';
require_once $srcDir . 'configs/databases.php';

if (empty($_GET['action'])) {
    Utils\setHttpCode(Utils\HTTP_CODE_NOT_FOUND);
    exit;
}

$allowedActions = array(
    'status' => 'loginStatus',
    'login' => 'login'
);

$action = strtolower($_GET['action']);

if (!isset($allowedActions[$action])) {
    Utils\setHttpCode(Utils\HTTP_CODE_NOT_FOUND);
    exit;
}

require_once $srcDir . 'api.actions.php';
Utils\setHttpCode(Utils\HTTP_CODE_OK);
$function = 'Api\\' . $allowedActions[$action];
$response = $function();

echo json_encode($response);
exit;