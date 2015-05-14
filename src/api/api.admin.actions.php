<?php

namespace Api\AdminActions;

function addAdmin() {
    return _addUser('admin');
}

function addClient() {
    return _addUser('client');
}

function addExecutor() {
    return _addUser('executor');
}

function _addUser($role) {
    if (!\Api\CommonActions\_isAuthorisedAs('admin')) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_UNAUTHORIZED);
        return array('route' => 'login');
    }
    if (!\Request\isPost()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $errors = \Utils\validateData($_POST, array(
        'email' => array(
            'required' => true,
            'type' => 'email',
            'messages' => array(
                'required' => \Dictionary\translate('Enter e-mail'),
                'regexp' => \Dictionary\translate('Invalid e-mail')
            )
        ),
        'password' => array(
            'required' => true,
            'messages' => array(
                'required' => \Dictionary\translate('Enter password'),
            )
        ),
        'is_active' => array(
            'required' => false,
            'type' => 'bool',
            'convert' => true,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        )
    ));
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, 'message' => \Dictionary\translate('Form contains invalid data'));
    }

    $user = array(
        'email' => strtolower($_POST['email']),
        'password' => \Utils\hashPassword($_POST['password']),
        'is_active' => $_POST['is_active'],
        'created_by' => $_SESSION['admin']['id'],
        'created_at' => time()
    );

    try {
        $user = \Db\insert($user, "`vktask1`.`{$role}s`");
        if ($user) {
            return $user;
        } else {
            \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
            return array('message' => \Dictionary\translate('Failed to save data to DB'));
        }
    } catch (\Exception $exc) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
        return array('message' => $exc->getMessage());
    }
}