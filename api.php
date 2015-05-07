<?php

require_once __DIR__ . '/src/configs/error.handling.php';
require_once __DIR__ . '/src/lib/http.codes.php';

if (empty($_GET['action'])) {
    HttpCodes::setHttpCode(HttpCodes::NOT_FOUND);
    exit;
}

$allowedActions = array(
    'status' => 'loginStatus',
    'login' => 'login'
);

$action = strtolower($_GET['action']);

if (!isset($allowedActions[$action])) {
    HttpCodes::setHttpCode(HttpCodes::NOT_FOUND);
    exit;
}

require_once __DIR__ . '/src/api.actions.php';
HttpCodes::setHttpCode(HttpCodes::OK);
$function = 'Api\\' . $allowedActions[$action];
$response = $function();

echo json_encode($response);
exit;