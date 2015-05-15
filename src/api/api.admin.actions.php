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
            'default' => true,
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
    );

    try {
        $user = \Db\insert($user, "`vktask1`.`{$role}s`");
        if (!empty($user)) {
            $user = array_intersect_key($user, array('id' => '', 'email' => '', 'is_active' => ''));
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

function updateAdmin() {
    return _updateUser('admin');
}

function updateClient() {
    return _updateUser('client');
}

function updateExecutor() {
    return _updateUser('executor');
}

function _updateUser($role) {
    if (!\Api\CommonActions\_isAuthorisedAs('admin')) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_UNAUTHORIZED);
        return array('route' => 'login');
    }
    if (!\Request\isPost()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $errors = \Utils\validateData($_POST, array(
        'id' => array(
            'required' => true,
            'type' => 'id',
            'convert' => true,
            'messages' => array(
                'required' => \Dictionary\translate('ID is required'),
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
        'is_active' => array(
            'required' => false,
            'convert' => true,
            'type' => 'bool',
            'remove_if_empty' => true,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        )
    ));
    $allowedFields = array('password', 'is_active');
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, 'message' => \Dictionary\translate('Form contains invalid data'));
    }
    $dataToUpdate = array_intersect_key($_POST, array_flip($allowedFields));
    if (empty($dataToUpdate['password'])) {
        unset($dataToUpdate['password']); ///< avoid saving empty password
    } else {
        $dataToUpdate['password'] = \Utils\hashPassword($dataToUpdate['password']);
    }
    if (empty($dataToUpdate)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('message' => \Dictionary\translate('No data passed'));
    }

    try {
        $table = "`vktask1`.`{$role}s`";
        if (!\Db\idExists($_POST['id'], $table)) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
            return array(
                'message' => \Dictionary\translate('Record with passed ID was not found in DB'),
                'errors' => array(
                    'id' => \Dictionary\translate('Invalid value')
                )
            );
        }
        $user = \Db\updateById($dataToUpdate, $table, $_POST['id']);
        if (!empty($user)) {
            $user = array_intersect_key($user, array('id' => '', 'email' => '', 'is_active' => ''));
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