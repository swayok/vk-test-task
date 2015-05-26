<?php

namespace Api\ExecutorActions;

function pendingTasksList() {
    return _tasksList('`executor_id` is NULL', 'created_at');
}

function executedTasksList() {
    return _tasksList('`executor_id` = :id', 'executed_at');
}

function _tasksList($conditions, $orderByColumn) {
    if (!\Api\CommonActions\_isAuthorisedAs('executor')) {
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

    $executor = \Api\CommonActions\_getAuthorisedUser();

    $fields = array('id', 'title', 'description', 'created_at', 'executed_at', 'paid_to_executor', 'executor_id');
    $executorPaymentRate = 1 - SYSTEM_COMISSION;
    $fields = '`t`.`' . implode('`,`t`.`', $fields) . "`, `payment` * $executorPaymentRate as `payment`";
    $clientFields = '`j`.`email` as `client_email`';
    $join = 'LEFT JOIN `vktask1`.`clients` as `j` ON `t`.`client_id` = `j`.id';
    $offset = (empty($errors) || empty($errors['page'])) ? ($_GET['page'] - 1) * DATA_GRID_ITEMS_PER_PAGE : 0;
    $options = "ORDER BY `t`.`$orderByColumn` DESC LIMIT " . DATA_GRID_ITEMS_PER_PAGE . ' OFFSET ' . $offset;
    $records = \Db\smartSelect(
        "SELECT $fields, $clientFields FROM `vktask2`.`tasks` as `t` $join WHERE $conditions $options",
        $executor
    );
    return $records;
}

function pendingTasksListInfo() {
    return _tasksListInfo('`executor_id` IS NULL');
}

function executedTasksListInfo() {
    return _tasksListInfo('`executor_id` = :id');
}

function _tasksListInfo($conditions) {
    if (!\Api\CommonActions\_isAuthorisedAs('executor')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isGet()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $executor = \Api\CommonActions\_getAuthorisedUser();
    $recordsCount = \Db\selectValue("SELECT COUNT(*) FROM `vktask2`.`tasks` WHERE $conditions", $executor);
    return array(
        'total' => $recordsCount,
        'pages' => ceil($recordsCount / DATA_GRID_ITEMS_PER_PAGE),
        'items_per_page' => DATA_GRID_ITEMS_PER_PAGE
    );
}

function executeTask() {
    if (!\Api\CommonActions\_isAuthorisedAs('executor')) {
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
    ));
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Form contains invalid data'));
    }

    try {
        $id = \Db\quoteValue($_POST['id']);
        $rows = \Db\select("SELECT `id`, `executor_id`, `payment` FROM `vktask2`.`tasks` WHERE `id` = $id");
        if (empty($rows)) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
            return array('_message' => \Dictionary\translate('Record with passed ID was not found in DB'));
        }
        $task = $rows[0];
        if (!empty($task['executor_id'])) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_CONFLICT);
            return array('_message' => \Dictionary\translate('Task have been already executed'));
        }
        $executor = \Api\CommonActions\_getAuthorisedUser();
        $paimentToExecutor = floatval($task['payment']) * (1 - SYSTEM_COMISSION);
        $paimentToExecutor = number_format(floor($paimentToExecutor * 100) / 100, 2, '.', '');
        $paimentToSystem = number_format(floatval($task['payment']) - $paimentToExecutor, 2, '.', '');
        $dataToUpdate = array(
            'executor_id' => $executor['id'],
            'executed_at' => date('Y-m-d H:i:s'),
            'paid_to_executor' => $paimentToExecutor,
            'paid_to_system' => $paimentToSystem
        );
        \Db\query('BEGIN');
        $task = \Db\updateById($dataToUpdate, '`vktask2`.`tasks`', $_POST['id']);
        if (!empty($task)) {
            $success = \Db\query(
                'UPDATE `vktask1`.`executors` ' .
                "SET `balance` = `balance` + {$task['paid_to_executor']} " .
                "WHERE `id` = {$executor['id']}"
            );
            if ($success) {
                \Db\query('COMMIT');
                $balance = \Db\selectValue("SELECT `balance` FROM `vktask1`.`executors` WHERE `id` = {$executor['id']}");
                $executor['balance'] = $balance;
                \Api\CommonActions\_setAuthorisedUser($executor);
                $fields = array('id', 'executed_at', 'executor_id', 'paid_to_executor', 'payment');
                $task = array_intersect_key($task, array_flip($fields));
                $task['_message'] = \Dictionary\translate('Task executed successfully');
                $task['balance'] = $balance;
                return $task;
            }
        }
        \Db\query('ROLLBACK');
        \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
        return array('_message' => \Dictionary\translate('Failed to save data to DB'));
    } catch (\Exception $exc) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
        return array('_message' => $exc->getMessage());
    }
}