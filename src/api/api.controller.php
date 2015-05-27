<?php

namespace Api\Controller;
use Utils;

if (session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once 'api.common.actions.php';

function runAction($action) {

    $allowedActions = array(
        'status' => '\Api\CommonActions\loginStatus',
        'login' => '\Api\CommonActions\login',
        'logout' => '\Api\CommonActions\logout',
    );

    $currentUser = \Api\CommonActions\_getAuthorisedUser();
    if (!empty($currentUser) && !empty($currentUser['role'])) {
        $allowedActions['update-profile'] = '\Api\CommonActions\updateProfile';
        switch ($currentUser['role']) {
            case 'admin':
                require_once 'api.admin.actions.php';
                $allowedActions += array(
                    'admins-list' => '\Api\AdminActions\adminsList',
                    'admins-list-info' => '\Api\AdminActions\adminsListInfo',
                    'admin' => '\Api\AdminActions\getAdmin',
                    'get-admin' => '\Api\AdminActions\getAdmin',
                    'add-admin' => '\Api\AdminActions\addAdmin',
                    'update-admin' => '\Api\AdminActions\updateAdmin',

                    'clients-list' => '\Api\AdminActions\clientsList',
                    'clients-list-info' => '\Api\AdminActions\clientsListInfo',
                    'client' => '\Api\AdminActions\getClient',
                    'get-client' => '\Api\AdminActions\getClient',
                    'add-client' => '\Api\AdminActions\addClient',
                    'update-client' => '\Api\AdminActions\updateClient',

                    'executors-list' => '\Api\AdminActions\executorsList',
                    'executors-list-info' => '\Api\AdminActions\executorsListInfo',
                    'executor' => '\Api\AdminActions\getExecutor',
                    'get-executor' => '\Api\AdminActions\getExecutor',
                    'add-executor' => '\Api\AdminActions\addExecutor',
                    'update-executor' => '\Api\AdminActions\updateExecutor',

                    'system-stats' => '\Api\AdminActions\systemStats'
                );
                break;
            case 'client':
                require_once 'api.client.actions.php';
                $allowedActions += array(
                    'get-task' => '\Api\ClientActions\getTask',
                    'add-task' => '\Api\ClientActions\addTask',
                    'update-task' => '\Api\ClientActions\updateTask',
                    'delete-task' => '\Api\ClientActions\deleteTask',
                    'client-tasks-list' => '\Api\ClientActions\tasksList',
                    'client-tasks-list-info' => '\Api\ClientActions\tasksListInfo',
                );
                break;
            case 'executor':
                require_once 'api.executor.actions.php';
                $allowedActions += array(
                    'pending-tasks-list' => '\Api\ExecutorActions\pendingTasksList',
                    'pending-tasks-list-info' => '\Api\ExecutorActions\pendingTasksListInfo',
                    'executed-tasks-list' => '\Api\ExecutorActions\executedTasksList',
                    'executed-tasks-list-info' => '\Api\ExecutorActions\executedTasksListInfo',
                    'execute-task' => '\Api\ExecutorActions\executeTask',
                );
                break;
            default:
                throw new \Exception("Unknown role [{$currentUser['role']}]");
        }
    }

    if (!isset($allowedActions[$action]) || !function_exists($allowedActions[$action])) {
        terminateUnauthorisedRequest();
    }

    Utils\setHttpCode(Utils\HTTP_CODE_OK);
    $ret = $allowedActions[$action]();
    if (!is_array($ret)) {
        $ret = array(
            '_message' => \Dictionary\translate('API action returned invalid response'),
            'errors' => array('response' => $ret)
        );
    }
    return $ret;
}

function terminateUnauthorisedRequest() {
    Utils\terminate(
        Utils\HTTP_CODE_UNAUTHORIZED,
        array('_message' => \Dictionary\translate('Access denied'), '_route' => 'login')
    );
}