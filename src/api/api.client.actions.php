<?php

namespace Api\ClientActions;

function addTask() {
    if (!\Api\CommonActions\_isAuthorisedAs('client')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isPost()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $validators = array(
        'title' => array(
            'required' => true,
            'messages' => array(
                'required' => \Dictionary\translate('Enter title'),
            )
        ),
        'description' => array(
            'required' => true,
            'messages' => array(
                'required' => \Dictionary\translate('Enter description'),
            )
        ),
        'payment' => array(
            'required' => true,
            'type' => 'float',
            'convert' => true,
            'default' => true,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
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
    );
    $errors = \Utils\validateData($_POST, $validators);
    if (!empty($errors['payment']) && $_POST['payment'] < MIN_TASK_PAYMENT) {
        $errors['payment'] = str_ireplace(':value', MIN_TASK_PAYMENT, \Dictionary\translate('Minimal payment is :value RUB'));
    }
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Form contains invalid data'));
    }

    $task = array_intersect_key($_POST, $validators);
    $client = \Api\CommonActions\_getAuthorisedUser();
    $task['client_id'] = $client['id'];

    try {
        $task = \Db\insert($task, '`vktask2`.`tasks`');
        if (!empty($task)) {
            $user['_message'] = \Dictionary\translate('Task created successfully');
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

function tasksList() {
    if (!\Api\CommonActions\_isAuthorisedAs('client')) {
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
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
    ));

    $client = \Api\CommonActions\_getAuthorisedUser();
    $offset = (empty($errors) || empty($errors['page'])) ? ($_GET['page'] - 1) * DATA_GRID_ITEMS_PER_PAGE : 0;
    $executorFields = '`j`.`email` as `executor_email`';
    $join = 'LEFT JOIN `vktask1`.`executors` as `j` ON `t`.`executor_id` = `j`.id';
    $where = 'WHERE `client_id` = :id';
    $options = 'ORDER BY `t`.`created_at` DESC LIMIT ' . DATA_GRID_ITEMS_PER_PAGE . ' OFFSET ' . $offset;
    $records = \Db\smartSelect("SELECT `t`.*, $executorFields FROM `vktask2`.`tasks` as `t` $join $where $options", $client);
    return $records;
}

function tasksListInfo() {
    if (!\Api\CommonActions\_isAuthorisedAs('client')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isGet()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $client = \Api\CommonActions\_getAuthorisedUser();
    $recordsCount = \Db\selectValue("SELECT COUNT(*) FROM `vktask2`.`tasks` WHERE `client_id` = :id", $client);
    return array(
        'total' => $recordsCount,
        'pages' => ceil($recordsCount / DATA_GRID_ITEMS_PER_PAGE),
        'items_per_page' => DATA_GRID_ITEMS_PER_PAGE
    );
}