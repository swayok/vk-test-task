<?php

namespace Api;

session_start();

function isPost() {
    return strtolower($_SERVER['REQUEST_METHOD']) === 'post';
}

require_once __DIR__ . '/configs/dictionary.php';

function loginStatus() {
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'] + array('view' => 'add_task');
    } else if (!empty($_SESSION['manager'])) {
        return $_SESSION['manager'] + array('view' => 'tasks_list');
    } else if (!empty($_SESSION['admin'])) {
        return $_SESSION['admin'] + array('view' => 'managers_list');
    } else {
        \Utils\setHttpCode(\Utils\HTTP_CODE_UNAUTHORIZED);
        return array(
            'message' => \Dictionary\translate('Authorisation required')
        );
    }
}

function login() {
    if (!isPost()) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $errors = array();
    if (empty($_POST['login'])) {
        $errors['login'] = \Dictionary\translate('Enter Login');
    }
    if (empty($_POST['password'])) {
        $errors['password'] = \Dictionary\translate('Enter Password');
    }
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors);
    }

    return array();
}