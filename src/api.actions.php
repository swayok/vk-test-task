<?php

namespace Api;

session_start();

function loginStatus() {
    if (!empty($_SESSION['client'])) {
        return $_SESSION['client'] + array('route' => 'add-task');
    } else if (!empty($_SESSION['executor'])) {
        return $_SESSION['executor'] + array('route' => 'tasks-list');
    } else if (!empty($_SESSION['admin'])) {
        return $_SESSION['admin'] + array('route' => 'managers-list');
    } else {
        \Utils\setHttpCode(\Utils\HTTP_CODE_UNAUTHORIZED);
        return array(
            'message' => \Dictionary\translate('Authorisation required')
        );
    }
}

function login() {
    if (!\Request\isPost()) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $errors = array();
    if (empty($_POST['role'])) {
        $errors['role'] = \Dictionary\translate('Select role');
    }
    if (empty($_POST['email'])) {
        $errors['email'] = \Dictionary\translate('Enter email');
    }
    if (empty($_POST['password'])) {
        $errors['password'] = \Dictionary\translate('Enter password');
    }
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, 'message' => \Dictionary\translate('Form contains invalid data'));
    }

    return array();
}