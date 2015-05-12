<?php

namespace Api;

session_start();

function loginStatus() {
    if (!empty($_SESSION['client'])) {
        return $_SESSION['client'];
    } else if (!empty($_SESSION['executor'])) {
        return $_SESSION['executor'];
    } else if (!empty($_SESSION['admin'])) {
        return $_SESSION['admin'];
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
    if (empty($_POST['role']) || !in_array($_POST['role'], array('admin', 'executor', 'client'))) {
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
    $table = $_POST['role'] . 's';
    $user = \Db\smartSelect(
        "SELECT * FROM `vktask1`.`{$table}` WHERE `email` = :email AND `password` = :password",
        array(
            'email' => strtolower($_POST['email']),
            'password' => \Utils\hashPassword($_POST['password'])
        )
    );
    if (!empty($user) && !empty($user[0])) {
        $user = $user[0];
        unset($user['password']);
        switch ($_POST['role']) {
            case 'admin':
                $user['route'] = 'admin_dashboard';
                break;
            case 'client':
                $user['route'] = 'add_task';
                break;
            case 'executor':
                $user['route'] = 'tasks_list';
                break;
        }
        $_SESSION[$_POST['role']] = $user;
        return $user;
    } else {
        \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
        return array(
            'message' => \Dictionary\translate('Authorisation error: user not found'),
            'errors' => array(
                'email' => \Dictionary\translate('Value not found'),
                'password' => \Dictionary\translate('Value not found')
            )
        );
    }
}