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
            'messages' => array(
                'required' => \Dictionary\translate('Enter payment for execution'),
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
    if (empty($errors['payment']) && $_POST['payment'] < MIN_TASK_PAYMENT) {
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
            $task = array_intersect_key($task, $validators + array('id' => ''));
            $task['_message'] = \Dictionary\translate('Task created successfully');
            return $task;
        } else {
            \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
            return array('_message' => \Dictionary\translate('Failed to save data to DB'));
        }
    } catch (\Exception $exc) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
        return array('_message' => $exc->getMessage());
    }
}


function updateTask () {
    if (!\Api\CommonActions\_isAuthorisedAs('client')) {
        \Api\Controller\terminateUnauthorisedRequest();
    }
    if (!\Request\isPost()) {
        \Utils\terminate(\Utils\HTTP_CODE_NOT_FOUND);
    }
    $validators = array(
        'id' => array(
            'required' => true,
            'type' => 'id',
            'convert' => true,
            'messages' => array(
                'required' => \Dictionary\translate('ID is required'),
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
        'title' => array(
            'required' => false,
            'remove_if_empty' => true,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
        'description' => array(
            'required' => false,
            'remove_if_empty' => true,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
        'payment' => array(
            'required' => false,
            'type' => 'float',
            'convert' => true,
            'remove_if_empty' => true,
            'messages' => array(
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
    );
    $errors = \Utils\validateData($_POST, $validators);
    if (empty($errors['payment']) && array_key_exists('payment', $_POST) && $_POST['payment'] < MIN_TASK_PAYMENT) {
        $errors['payment'] = str_ireplace(':value', MIN_TASK_PAYMENT, \Dictionary\translate('Minimal payment is :value RUB'));
    }
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Form contains invalid data'));
    }
    $dataToUpdate = array_intersect_key($_POST, $validators);
    unset($dataToUpdate['id']);
    if (empty($dataToUpdate)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('_message' => \Dictionary\translate('No data passed'));
    }

    try {
        $client = \Api\CommonActions\_getAuthorisedUser();
        $rows = \Db\smartSelect(
            "SELECT `id`, `executor_id` FROM `vktask2`.`tasks` WHERE `id` = :id AND `client_id` = :client_id",
            array('id' => $_POST['id'], 'client_id' => $client['id'])
        );
        if (empty($rows)) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
            return array(
                '_message' => \Dictionary\translate('Record with passed ID was not found in DB'),
                'errors' => array(
                    'id' => \Dictionary\translate('Invalid value')
                )
            );
        } else if ($rows[0]['executor_id'] > 0) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
            return array(
                '_message' => \Dictionary\translate('Task have been already executed'),
            );
        }
        $task = \Db\updateById($dataToUpdate, '`vktask2`.`tasks`', $_POST['id']);
        if (!empty($task)) {
            $task = array_intersect_key($task, $validators);
            $task['_message'] = \Dictionary\translate('Task updated successfully');
            return $task;
        } else {
            \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
            return array('_message' => \Dictionary\translate('Failed to save data to DB'));
        }
    } catch (\Exception $exc) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INTERNAL_SERVER_ERRORR);
        return array('_message' => $exc->getMessage());
    }
}

function getTask() {
    if (!\Api\CommonActions\_isAuthorisedAs('client')) {
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
        'not_executed' => array(
            'required' => false,
            'type' => 'bool',
            'convert' => true,
            'default' => false,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        )
    ));
    if (!empty($errors)) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
        return array('errors' => $errors, '_message' => \Dictionary\translate('Invalid request data'));
    }
    try {
        $client = \Api\CommonActions\_getAuthorisedUser();
        $rows = \Db\smartSelect("SELECT * FROM `vktask2`.`tasks` WHERE `id` = :id AND client_id = :client_id", array(
            'id' => $_GET['id'],
            'client_id' => $client['id']
        ));
        if (empty($rows)) {
            \Utils\setHttpCode(\Utils\HTTP_CODE_NOT_FOUND);
            return array(
                '_message' => \Dictionary\translate('Record with passed ID was not found in DB'),
                'errors' => array(
                    'id' => \Dictionary\translate('Invalid value')
                )
            );
        } else {
            $task = $rows[0];
            if (!empty($_GET['not_executed']) && $task['executor_id'] > 0) {
                \Utils\setHttpCode(\Utils\HTTP_CODE_INVALID);
                return array(
                    '_message' => \Dictionary\translate('Task already exetuted'),
                );
            }
            $fields = array_flip(array(
                'id', 'title', 'description', 'payment', 'client_id',
                'is_active', 'executor_id', 'created_at', 'executed_at'
            ));
            $task = array_intersect_key($task, $fields);
            return $task;
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
            'default' => 1,
            'messages' => array(
                'type' => \Dictionary\translate('Invalid value'),
            )
        ),
    ));

    $client = \Api\CommonActions\_getAuthorisedUser();
    $fields = array(
        'id', 'title', 'description', 'payment', 'client_id',
        'is_active', 'executor_id', 'created_at', 'executed_at'
    );
    $fields = '`t`.`' . implode('`, `t`.`', $fields) . '`';
    $executorFields = '`j`.`email` as `executor_email`';
    $join = 'LEFT JOIN `vktask1`.`executors` as `j` ON `t`.`executor_id` = `j`.id';
    $where = 'WHERE `client_id` = :id';
    $offset = (empty($errors) || empty($errors['page'])) ? ($_GET['page'] - 1) * DATA_GRID_ITEMS_PER_PAGE : 0;
    $options = 'ORDER BY `t`.`created_at` DESC LIMIT ' . DATA_GRID_ITEMS_PER_PAGE . ' OFFSET ' . $offset;
    $records = \Db\smartSelect("SELECT $fields, $executorFields FROM `vktask2`.`tasks` as `t` $join $where $options", $client);
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