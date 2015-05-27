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
        \Api\Controller\terminateUnauthorisedRequest();
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
                'type' => \Dictionary\translate('Invalid e-mail')
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
        return array('errors' => $errors, '_message' => \Dictionary\translate('Form contains invalid data'));
    }
    $table = "`vktask1`.`{$role}s`";
    if (\Db\selectValue("SELECT `id` FROM {$table} WHERE `email` = :email", array('email' => $_POST['email'])) > 0) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array(
            'errors' => array('email' => \Dictionary\translate('Account with entered e-mail address already exists')),
            '_message' => \Dictionary\translate('Form contains invalid data')
        );
    }

    $admin = \Api\CommonActions\_getAuthorisedUser();
    $user = array(
        'email' => strtolower($_POST['email']),
        'password' => \Utils\hashPassword($_POST['password']),
        'is_active' => $_POST['is_active'],
        'created_by' => $admin['id'],
    );

    try {
        $user = \Db\insert($user, $table);
        if (!empty($user)) {
            $user = array_intersect_key($user, array('id' => '', 'email' => '', 'is_active' => ''));
            $user['_message'] = \Dictionary\translate(ucfirst($role) . '\'s account created successfully');
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

function getAdmin() {
    return _getUser('admin', array('id', 'email', 'is_active'));
}

function getClient() {
    return _getUser('client', array('id', 'email', 'is_active'));
}

function getExecutor() {
    return _getUser('executor', array('id', 'email', 'is_active'));
}

function _getUser($role, $fields) {
    if (!\Api\CommonActions\_isAuthorisedAs('admin')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isGet()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $errors = \Utils\validateData($_GET, array(
        'id' => array(
            'required' => true,
            'type' => 'id',
            'convert' => true,
            'messages' => array(
                'required' => \Dictionary\translate('ID is required'),
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
    ));
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Invalid request data'));
    }
    try {
        $fields = '`' . implode('`,`', $fields) . '`';
        $id = \Db\quoteValue($_GET['id']);
        $rows = \Db\select("SELECT $fields FROM `vktask1`.`{$role}s` WHERE `id` = $id");
        if (empty($rows)) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
            return array(
                '_message' => \Dictionary\translate('Record with passed ID was not found in DB'),
                'errors' => array(
                    'id' => \Dictionary\translate('Invalid value')
                )
            );
        } else {
            return $rows[0];
        }
    } catch (\Exception $exc) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
        return array('_message' => $exc->getMessage());
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
        \Api\Controller\terminateUnauthorisedRequest();
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
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Form contains invalid data'));
    }
    $allowedFields = array('password', 'is_active');
    $dataToUpdate = array_intersect_key($_POST, array_flip($allowedFields));
    if (empty($dataToUpdate['password'])) {
        unset($dataToUpdate['password']); ///< avoid saving empty password
    } else {
        $dataToUpdate['password'] = \Utils\hashPassword($dataToUpdate['password']);
    }
    if (empty($dataToUpdate)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('_message' => \Dictionary\translate('No data passed'));
    }

    try {
        $table = "`vktask1`.`{$role}s`";
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
            $user = array_intersect_key($user, array('id' => '', 'email' => '', 'is_active' => ''));
            $user['_message'] = \Dictionary\translate(ucfirst($role) . '\'s account updated successfully');
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

function clientsList() {
    return usersList('client', array('id', 'email', 'is_active', 'created_by', 'created_at'));
}

function adminsList() {
    return usersList('admin', array('id', 'email', 'is_active', 'created_by', 'created_at'));
}

function executorsList() {
    return usersList('executor', array('id', 'email', 'is_active', 'created_by', 'created_at', 'balance'));
}

function usersList($role, array $fields) {
    if (!\Api\CommonActions\_isAuthorisedAs('admin')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isGet()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $errors = \Utils\validateData($_GET, array(
        'page' => array(
            'required' => false,
            'type' => 'id',
            'convert' => true,
            'default' => 1,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
    ));

    $mainFeilds = '`t`.`' . implode('`,`t`.`', $fields) . '`';
    $adminCreatorFields = '`j`.`id` as `creator_id`, `j`.`email` as `creator_email`';
    $join = 'LEFT JOIN `vktask1`.`admins` as `j` ON `t`.`created_by` = `j`.id';
    $where = '';
    $offset = (empty($errors) || empty($errors['page'])) ? ($_GET['page'] - 1) * DATA_GRID_ITEMS_PER_PAGE : 0;
    $options = 'ORDER BY `t`.`created_at` DESC LIMIT ' . DATA_GRID_ITEMS_PER_PAGE . ' OFFSET ' . $offset;
    $records = \Db\select("SELECT {$mainFeilds}, {$adminCreatorFields} FROM `vktask1`.`{$role}s` as `t` $join $where $options");
    return $records;
}

function clientsListInfo() {
    return usersListInfo('client');
}

function adminsListInfo() {
    return usersListInfo('admin');
}

function executorsListInfo() {
    return usersListInfo('executor');
}

function usersListInfo($role) {
    if (!\Api\CommonActions\_isAuthorisedAs('admin')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isGet()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $recordsCount = \Db\selectValue("SELECT COUNT(*) FROM `vktask1`.`{$role}s`");
    return array(
        'total' => $recordsCount,
        'pages' => ceil($recordsCount / DATA_GRID_ITEMS_PER_PAGE),
        'items_per_page' => DATA_GRID_ITEMS_PER_PAGE
    );
}

function systemStats() {
    if (!\Api\CommonActions\_isAuthorisedAs('admin')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isGet()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $todayStart = \Db\quoteValue(strtotime(date('d-m-Y 00:00:00')));
    $todayEnd = \Db\quoteValue(strtotime(date('d-m-Y 23:59:59')));
    $todayBetween = "BETWEEN $todayStart AND $todayEnd";
    $yesterdayStart = \Db\quoteValue(strtotime(date('d-m-Y 00:00:00', strtotime('-1 day'))));
    $yesterdayEnd = \Db\quoteValue(strtotime(date('d-m-Y 23:59:59', strtotime('-1 day'))));
    $yesterdayBetween = "BETWEEN $yesterdayStart AND $yesterdayEnd";
    $ret = array(
        'tasks_total' => \Db\selectValue('SELECT COUNT(*) FROM `vktask2`.`tasks`'),
        'tasks_added_today' => \Db\selectValue("SELECT COUNT(*) FROM `vktask2`.`tasks` WHERE UNIX_TIMESTAMP(`created_at`) $todayBetween"),
        'tasks_added_yesterday' => \Db\selectValue("SELECT COUNT(*) FROM `vktask2`.`tasks` WHERE UNIX_TIMESTAMP(`created_at`) $yesterdayBetween"),
        'tasks_pending_total' => \Db\selectValue("SELECT COUNT(*) FROM `vktask2`.`tasks` WHERE `is_active` = 1 AND `executor_id` IS NULL"),
    );
    $rows = \Db\select("SELECT COUNT(*) as 'tasks_executed_total', SUM(`paid_to_system`) as 'system_earned_total', " .
        "SUM(`paid_to_executor`) as 'executors_earned_total' FROM `vktask2`.`tasks` WHERE `executor_id` IS NOT NULL"
    );
    $ret += $rows[0];
    $rows = \Db\select("SELECT COUNT(*) as 'tasks_executed_today', SUM(`paid_to_system`) as 'system_earned_today', " .
        "SUM(`paid_to_executor`) as 'executors_earned_today' FROM `vktask2`.`tasks` " .
        "WHERE `executor_id` IS NOT NULL AND UNIX_TIMESTAMP(`executed_at`) $todayBetween"
    );
    $ret += $rows[0];
    $rows = \Db\select("SELECT COUNT(*) as 'tasks_executed_yesterday', SUM(`paid_to_system`) as 'system_earned_yesterday', " .
        "SUM(`paid_to_executor`) as 'executors_earned_yesterday' FROM `vktask2`.`tasks` " .
        "WHERE `executor_id` IS NOT NULL AND UNIX_TIMESTAMP(`executed_at`) $yesterdayBetween"
    );
    $ret += $rows[0];
    foreach ($ret as $key => &$value) {
        if (empty($value)) {
            $value = '0';
        }
    }
    return $ret;
}