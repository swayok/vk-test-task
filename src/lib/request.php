<?php

namespace Request;

$__REQUEST_INFO = array(
    'isPost' => false,
    'isGet' => false,
    'isAjax' => false
);

if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    $__REQUEST_INFO['isPost'] = true;
} else {
    $__REQUEST_INFO['isGet'] = true;
}
$__REQUEST_INFO['isAjax'] = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

function isPost() {
    return $GLOBALS['__REQUEST_INFO']['isPost'];
}

function isGet() {
    return $GLOBALS['__REQUEST_INFO']['isGet'];
}

function isAjax() {
    return $GLOBALS['__REQUEST_INFO']['isAjax'];
}