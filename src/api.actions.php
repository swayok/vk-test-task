<?php

namespace Api;

session_start();

function isPost() {
    return strtolower($_SERVER['REQUEST_METHOD']) === 'post';
}

require_once __DIR__ . '/configs/strings.php';

function loginStatus() {
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'] + array('view' => 'add_task');
    } else if (!empty($_SESSION['manager'])) {
        return $_SESSION['manager'] + array('view' => 'tasks_list');
    } else if (!empty($_SESSION['admin'])) {
        return $_SESSION['admin'] + array('view' => 'managers_list');
    } else {
        \HttpCodes::setHttpCode(\HttpCodes::UNAUTHORIZED);
        return array(
            'message' => \Strings::translate('Authorisation required')
        );
    }
}

function login() {
    if (!isPost()) {
        \HttpCodes::setHttpCode(\HttpCodes::NOT_FOUND);
    }
    $errors = array();
    if (empty($_POST['login'])) {
        $errors['login'] = \Strings::translate('Enter Login');
    }
    if (empty($_POST['password'])) {
        $errors['password'] = \Strings::translate('Enter Password');
    }
    if (!empty($errors)) {
        \HttpCodes::setHttpCode(\HttpCodes::INVALID);
        return array('errors' => $errors);
    }

    return array();
}