<?php

namespace Api\CommonActions;

function loginStatus() {
    if (!\Request\isGet()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $user = _getAuthorisedUser();
    if (empty($user)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_UNAUTHORIZED);
        return array(
            '_message' => \Dictionary\translate('Authorisation required')
        );
    } else {
        return $user;
    }
}

/**
 * @return bool|array
 */
function _getAuthorisedUser() {
    if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
        return $_SESSION['user'];
    } else {
        return false;
    }
}

function _setAuthorisedUser($userData) {
    $_SESSION['user'] = $userData;
}

function _unsetAuthorisation() {
    unset($_SESSION['user']);
}

function _isAuthorisedAs($role) {
    if (!in_array($role, array('admin', 'executor', 'client'))) {
        throw new \Exception("Unknown role [{$role}]");
    }
    return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']) && $_SESSION['user']['role'] === $role;
}

function login() {
    if (!\Request\isPost()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $errors = \Utils\validateData($_POST, array(
        'role' => array(
            'required' => true,
            'regexp' => '%^admin|executor|client$%i',
            'messages' => array(
                'required' => \Dictionary\translate('Select role'),
                'regexp' => \Dictionary\translate('Select role')
            )
        ),
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
        )
    ));
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Form contains invalid data'));
    }

    _unsetAuthorisation();
    $userRole = strtolower($_POST['role']);
    $table = $userRole . 's';
    $fields = '`id`, `email`';
    if ($userRole === 'executor') {
        $fields .= ', `balance`';
    }
    $user = \Db\smartSelect(
        "SELECT $fields FROM `vktask1`.`{$table}` WHERE `is_active` = :is_active AND `email` = :email AND `password` = :password",
        array(
            'is_active' => true,
            'email' => strtolower($_POST['email']),
            'password' => \Utils\hashPassword($_POST['password']),
        )
    );
    if (!empty($user) && !empty($user[0])) {
        $user = $user[0];
        unset($user['password']);
        switch ($userRole) {
            case 'admin':
                $user['_route'] = 'admin-dashboard';
                break;
            case 'client':
                $user['_route'] = 'client-tasks-list';
                break;
            case 'executor':
                $user['_route'] = 'executor-pending-tasks-list';
                break;
        }
        $user['role'] = $userRole;
        _setAuthorisedUser($user);
        return $user;
    } else {
        \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
        return array(
            '_message' => \Dictionary\translate('Authorisation error: user not found'),
            'errors' => array(
                'email' => \Dictionary\translate('Value not found'),
                'password' => \Dictionary\translate('Value not found')
            )
        );
    }
}

function logout() {
    _unsetAuthorisation();
    return array('_route' => 'login');
}

function updateProfile() {
    if (!\Request\isPost()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $currentUser = _getAuthorisedUser();
    if (!$currentUser) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    $errors = \Utils\validateData($_POST, array(
        'id' => array(
            'required' => true,
            'type' => 'id',
            'regexp' => "%^{$currentUser['id']}$%",
            'convert' => true,
            'messages' => array(
                'required' => \Dictionary\translate('ID is required'),
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
        'role' => array(
            'required' => true,
            'convert' => false,
            'regexp' => "%^{$currentUser['role']}$%i",
            'remove_if_empty' => true,
            'messages' => array(
                'required' => \Dictionary\translate('Role is required'),
                'regexp' => \Dictionary\translate('Invalid value'),
            )
        )
    ));
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Form data does not match authorised user data'));
    }
    $allowedFields = array('password');
    $dataToUpdate = array_intersect_key($_POST, array_flip($allowedFields));
    if (empty($dataToUpdate['password'])) {
        unset($dataToUpdate['password']); ///< avoid saving empty password
    } else {
        $dataToUpdate['password'] = \Utils\hashPassword($dataToUpdate['password']);
    }
    if (empty($dataToUpdate)) {
        $currentUser['_message'] = \Dictionary\translate('Your account updated successfully');
        return $currentUser;
    }

    try {
        $table = "`vktask1`.`{$currentUser['role']}s`";
        if (!\Db\idExists($_POST['id'], $table)) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
            return array(
                '_message' => \Dictionary\translate('Record with passed ID was not found in DB'),
                'errors' => array(
                    'id' => \Dictionary\translate('Invalid value')
                )
            );
        }
        $user = \Db\updateById($dataToUpdate, $table, $_POST['id']);
        if (!empty($user)) {
            $user = array_replace(
                $currentUser,
                array_intersect_key($user, array('id' => '', 'email' => ''))
            );
            _setAuthorisedUser($user);
            $user['_message'] = \Dictionary\translate('Your account updated successfully');
            return $user;
        } else {
            \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
            return array('_message' => \Dictionary\translate('Failed to save data to DB'));
        }
    } catch (\Exception $exc) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
        return array('_message' => $exc->getMessage());
    }
}