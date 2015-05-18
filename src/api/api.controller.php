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
        switch ($currentUser['role']) {
            case 'admin':
                require_once 'api.admin.actions.php';
                $allowedActions += array(
                    'admins-list' => '\Api\AdminActions\adminsList',
                    'admin' => '\Api\AdminActions\getAdmin',
                    'add-admin' => '\Api\AdminActions\addAdmin',
                    'update-admin' => '\Api\AdminActions\updateAdmin',

                    'clients-list' => '\Api\AdminActions\clientsList',
                    'client' => '\Api\AdminActions\getClient',
                    'add-client' => '\Api\AdminActions\addClient',
                    'update-client' => '\Api\AdminActions\updateClient',

                    'executors-list' => '\Api\AdminActions\executorsList',
                    'executor' => '\Api\AdminActions\getExecutor',
                    'add-executor' => '\Api\AdminActions\addExecutor',
                    'update-executor' => '\Api\AdminActions\updateExecutor',
                );
                break;
            case 'client':
                require_once 'api.client.actions.php';
                $allowedActions += array(
                    'add-task' => '\Api\ClientActions\addTask',
                    'my-task' => '\Api\ClientActions\myTasks',
                );
                break;
            case 'executor':
                require_once 'api.executor.actions.php';
                $allowedActions += array(
                    'tasks-list' => '\Api\ExecutorActions\getActiveTasks',
                    'execute-task' => '\Api\ExecutorActions\executeTask',
                    'balance' => '\Api\ExecutorActions\getBalance'
                );
                break;
            default:
                throw new \Exception("'Unknown role [{$currentUser['role']}]'");
        }
    }

    if (!isset($allowedActions[$action]) || !function_exists($allowedActions[$action])) {
        Utils\setHttpCode(Utils\HTTP_CODE_NOT_FOUND);
        exit;
    }

    Utils\setHttpCode(Utils\HTTP_CODE_OK);
    return $allowedActions[$action]();
}