<?php

namespace Tests\Api;

require_once __DIR__ . '/../lib/test.tools.php';
require_once __DIR__ . '/../configs/databases.php';
require_once __DIR__ . '/../api/api.controller.php';
require_once __DIR__ . '/../api/api.admin.actions.php';
require_once __DIR__ . '/../api/api.client.actions.php';
require_once __DIR__ . '/../api/api.executor.actions.php';

function getTestsList() {
    return array(
        'Login status & is authorised as role' => __NAMESPACE__ . '\loginStatus',
        'Admin: Users management, authorisation and data retrieving' => __NAMESPACE__ . '\usersManagementAndDataRetrieving',
        'Admin: system stats' => __NAMESPACE__ . '\adminSystemStats',
        'Client: Tasks management and data retrieving' => __NAMESPACE__ . '\clientTasksManagementAndDataRetrieving',
        'Executor: Tasks execution and data retrieving' => __NAMESPACE__ . '\executorTasksManagementAndDataRetrieving',
    );
}

$_TEST_USERS = array();

function getTestUser($role) {
    $email = 'user_for_tests@test.com';
    if (empty($GLOBALS['_TEST_USERS'][$role])) {
        $table = "`vktask1`.`{$role}s`";
        $fields = '`id`, `email`, `is_active`';
        if ($role === 'executor') {
            $fields .= ', `balance`';
        }
        $rows = \Db\smartSelect(
            "SELECT $fields FROM $table WHERE `email` = :email",
            array('email' => $email)
        );
        if (empty($rows)) {
            $user = \Db\insert(array(
                'email' => $email,
                'password' => \Utils\hashPassword('l9DFhc1cXHSot4OkxZj1'),
                'is_active' => true
            ), $table);
            if (empty($user)) {
                throw new \Exception('Unable to insert test user to DB');
            }
        } else {
            $user = $rows[0];
            if ($user['is_active'] == '0') {
                $success = \Db\query("UPDATE $table SET `is_active` = 1 WHERE `id` = {$user['id']}");
                if (!$success) {
                    throw new \Exception('Unable to activate test user');
                }
            }
        }
        $user['role'] = $role;
        $GLOBALS['_TEST_USERS'][$role] = $user;
    }
    return $GLOBALS['_TEST_USERS'][$role];
}

function loginStatus() {
    \TestTools\cleanTestResults();

    // noone logged in

    \Api\CommonActions\_unsetAuthorisation();
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('_message'))
    );
    \TestTools\addTestResult('no users logged in', $success, $response);

    \Api\CommonActions\_setAuthorisedUser(array());
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('_message'))
    );
    \TestTools\addTestResult('no users logged in / empty user data', $success, $response);

    _testIsAuthorisedAs(false, false, false);

    \Api\CommonActions\_unsetAuthorisation();

    // someone logged in

    $admin = array(
        'id' => '1',
        'email' => 'admin@test.ru',
        'role' => 'admin',
        '_route' => 'admin-dashboard'
    );
    $client = array(
        'id' => '1',
        'email' => 'client@test.ru',
        'role' => 'client',
        '_route' => 'client-tasks-list'
    );
    $executor = array(
        'id' => '1',
        'email' => 'executor@test.ru',
        'role' => 'executor',
        '_route' => 'executor-pending-tasks-list',
        'balance' => 111.11
    );

    \Api\CommonActions\_setAuthorisedUser($admin);
    _testIsAuthorisedAs(false, false, true);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($admin))
        && \TestTools\assertEquals($response['email'], $admin['email'])
        && \TestTools\assertEquals($response['role'], $admin['role'])
    );
    \TestTools\addTestResult('admin logged in', $success, $response);

    \Api\CommonActions\_setAuthorisedUser($executor);
    _testIsAuthorisedAs(false, true, false);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($executor))
        && \TestTools\assertEquals($response['email'], $executor['email'])
        && \TestTools\assertEquals($response['role'], $executor['role'])
        && \TestTools\assertEquals($response['balance'], $executor['balance'])
    );
    \TestTools\addTestResult('executor logged in', $success, $response);

    \Api\CommonActions\_setAuthorisedUser($client);
    _testIsAuthorisedAs(true, false, false);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    \TestTools\addTestResult('client logged in', $success, $response);

    try {
        $response = \Api\CommonActions\_isAuthorisedAs('qq');
        \TestTools\addTestResult('is authorised as qqq: exception', false, 'Fail. Exception should be thrown!');
    } catch (\Exception $exc) {
        \TestTools\addTestResult('is authorised as qqq: exception', true, $response);
    }

    return \TestTools\getTestResults(true);
}

function _testIsAuthorisedAs($client = false, $executor = false, $admin = false) {
    $code = intval($client) . intval($executor) . intval($admin);
    $response = \Api\CommonActions\_isAuthorisedAs('client');
    $success = (
        \TestTools\assertEquals($response, $client)
    );
    \TestTools\addTestResult($code . ' / is authorised as client: ' . ($client ? 'true' : 'false'), $success, $response);

    $response = \Api\CommonActions\_isAuthorisedAs('executor');
    $success = (
        \TestTools\assertEquals($response, $executor)
    );
    \TestTools\addTestResult($code . ' / is authorised as executor: ' . ($executor ? 'true' : 'false'), $success, $response);

    $response = \Api\CommonActions\_isAuthorisedAs('admin');
    $success = (
        \TestTools\assertEquals($response, $admin)
    );
    \TestTools\addTestResult($code . ' / is authorised as admin: ' . ($admin ? 'true' : 'false'), $success, $response);
}

function usersManagementAndDataRetrieving() {
    \TestTools\cleanTestResults();
    \Api\CommonActions\_unsetAuthorisation();
    $GLOBALS['__REQUEST_INFO']['isPost'] = true;
    $GLOBALS['__REQUEST_INFO']['isGet'] = false;
    $_POST = array();
    $_GET = array();

    try {
        $response = \Api\AdminActions\addAdmin();
        \TestTools\addTestResult('admin not logged in: exception', false, $response);
    } catch (\Exception $exc) {
        $success = (
            \TestTools\assertEquals($exc->getCode(), 401)
            && \TestTools\assertHasKeys(json_decode($exc->getMessage(), true), array('_message', '_route'))
        );
        \TestTools\addTestResult('admin not logged in: exception', $success, array(
            'message' => $exc->getMessage(),
            'exc' => $exc->getCode(),
            'trace' => $exc->getTraceAsString()
        ));
    }

    $admin = getTestUser('admin');
    \Api\CommonActions\_setAuthorisedUser($admin);
    $validUser = array(
        'email' => 'testuser' . time() . '@test.com',
        'password' => 'l9DFhc1cXHSot4OkxZj1',
    );

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array();
    $response = \Api\AdminActions\addAdmin();
    $success = (
        \TestTools\assertValidationErrors($response, array('email', 'password'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('user creation: empty post data', $success, $response);

    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertValidationErrors($response, array('email', 'password', 'role'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('login: empty post data', $success, $response);

    $_POST = array(
        'email' => '',
        'password' => '',
        'is_active' => ''
    );

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addClient();
    $success = (
        \TestTools\assertValidationErrors($response, array('email', 'password'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('user creation: empty values', $success, $response);

    $_POST = array(
        'email' => '',
        'password' => '',
        'role' => ''
    );
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertValidationErrors($response, array('email', 'password', 'role'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('login: empty values', $success, $response);

    $_POST = array(
        'email' => 'qq',
        'password' => 'qq',
        'is_active' => 'true'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addExecutor();
    $success = (
        \TestTools\assertValidationErrors($response, array('email', 'is_active'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('user creation: invalid values', $success, $response);

    $_POST = array(
        'email' => 'qq',
        'password' => 'qq',
        'role' => 'qq'
    );
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertValidationErrors($response, array('email', 'role'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('login: invalid values', $success, $response);

    // users creation, login, deactivation, get single user data, get list of users and lists info

    $done = _testUserRole('client', $validUser, $admin);
    if (!$done) {
        return \TestTools\getTestResults(true);
    }

    $done = _testUserRole('executor', $validUser, $admin);
    if (!$done) {
        return \TestTools\getTestResults(true);
    }

    $done = _testUserRole('admin', $validUser, $admin);
    if (!$done) {
        return \TestTools\getTestResults(true);
    }

    return \TestTools\getTestResults(true);
}

function _testUserRole($role, $validUser, $admin) {
    $validUser['password'] .= $role;

    $ucRole = ucfirst($role);
    $addUserFn = "Api\\AdminActions\\add{$ucRole}";
    $updateUserFn = "Api\\AdminActions\\update{$ucRole}";
    $getUserFn = "Api\\AdminActions\\get{$ucRole}";
    $usersListFn = "Api\\AdminActions\\{$role}sList";
    $usersListInfoFn = "Api\\AdminActions\\{$role}sListInfo";

    $GLOBALS['__REQUEST_INFO']['isPost'] = true;
    $GLOBALS['__REQUEST_INFO']['isGet'] = false;
    $_GET = array();

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $addUserFn();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active', '_message'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    \TestTools\addTestResult("$role creation", $success, $response);
    if (!$success) {
        return false;
    }
    $userId = $response['id'];

    $_POST = array();
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $updateUserFn();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult("$role update: no data", $success, $response);

    $_POST = array(
        'id' => '',
        'is_active' => '',
        'password' => ''
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $updateUserFn();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult("$role update: empty values", $success, $response);

    $_POST = array(
        'id' => 'qq',
        'is_active' => 'qq',
        'password' => ''
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $updateUserFn();
    $success = (
        \TestTools\assertValidationErrors($response, array('id', 'is_active'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult("$role update: invalid values", $success, $response);

    $notExistsingId = intval(\Db\selectValue("SELECT MAX(`id`) FROM `vktask1`.`{$role}s`")) + 90000;
    $_POST = array(
        'id' => $notExistsingId,
        'is_active' => '0',
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $updateUserFn();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
        && \TestTools\assertHasKeys($response['errors'], array('id'))
    );
    \TestTools\addTestResult("$role update: invalid id", $success, $response);

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $addUserFn();
    $success = (
        \TestTools\assertValidationErrors($response, array('email'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult("$role creation: duplicate email", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = $role === 'client' ? 'executor' : 'client';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult("$role login: wrong role", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST['role'] = $role;
    $expectedFields = array('id', 'email', '_route', 'role');
    if ($role === 'executor') {
        $expectedFields[] = 'balance';
    }
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertHasKeys($response, $expectedFields)
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['role'], $role)
    );
    \TestTools\addTestResult("$role login", $success, $response);
    \Api\CommonActions\_setAuthorisedUser($admin);

    $_POST = array(
        'id' => $userId,
        'is_active' => '0'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $updateUserFn();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active', '_message'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $userId)
        && \TestTools\assertEquals($response['is_active'], '0')
    );
    \TestTools\addTestResult("$role deactivation", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = $role;
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult("deactivated $role login", $success, $response);
    \Api\CommonActions\_setAuthorisedUser($admin);

    $_POST = array(
        'id' => $userId,
        'is_active' => '1'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = $updateUserFn();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active', '_message'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $userId)
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    \TestTools\addTestResult("$role activation", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $_POST = array();
    try {
        $response = $getUserFn();
        \TestTools\addTestResult("$role get data: POST request (exception)", false, 'Exception expected');
    } catch (\Exception $exc) {
        $success = (
            \TestTools\assertEquals($exc->getCode(), \Utils\HTTP_CODE_NOT_FOUND)
        );
        \TestTools\addTestResult("$role get data: POST request (exception)", $success, $response);
    }

    $GLOBALS['__REQUEST_INFO']['isPost'] = false;
    $GLOBALS['__REQUEST_INFO']['isGet'] = true;
    $_POST = array();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = $getUserFn();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
    );
    \TestTools\addTestResult("$role get data: no id passed", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('id' => '-1');
    $response = $getUserFn();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
    );
    \TestTools\addTestResult("$role get data: invalid id passed", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('id' => $userId);
    $response = $getUserFn();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $userId)
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    \TestTools\addTestResult("$role get data", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = $usersListFn();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], array('id', 'email', 'is_active'), false)
    );
    \TestTools\addTestResult("$role get records list: without page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '-1');
    $response = $usersListFn();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], array('id', 'email', 'is_active'), false)
    );
    \TestTools\addTestResult("$role get records list: with invalid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '2');
    $response = $usersListFn();
    $success = (
        \TestTools\assertEquals(count($response), 0)
        || (
            \TestTools\assertHasKeys($response, array(0), false)
            && \TestTools\assertHasKeys($response[0], array('id', 'email', 'is_active'), false)
        )
    );
    \TestTools\addTestResult("$role get records list: with valid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = $usersListInfoFn();
    $success = (
        \TestTools\assertHasKeys($response, array('total', 'pages', 'items_per_page'))
        && \TestTools\assertEquals($response['total'] > 0, true)
        && \TestTools\assertEquals($response['pages'] > 0, true)
        && \TestTools\assertEquals($response['items_per_page'] > 0, true)
    );
    \TestTools\addTestResult("$role get records list info", $success, $response);

    \Db\query("DELETE FROM `vktask1`.`{$role}s` WHERE `email` LIKE 'testuser%@test.com'");
    return true;
}

function clientTasksManagementAndDataRetrieving() {
    \TestTools\cleanTestResults();
    \Api\CommonActions\_unsetAuthorisation();
    $GLOBALS['__REQUEST_INFO']['isPost'] = true;
    $GLOBALS['__REQUEST_INFO']['isGet'] = false;
    $_POST = array();
    $_GET = array();

    try {
        $response = \Api\ClientActions\addTask();
        \TestTools\addTestResult('client not logged in: exception', false, $response);
    } catch (\Exception $exc) {
        $success = (
            \TestTools\assertEquals($exc->getCode(), 401)
            && \TestTools\assertHasKeys(json_decode($exc->getMessage(), true), array('_message', '_route'))
        );
        \TestTools\addTestResult('client not logged in: exception', $success, array(
            'message' => $exc->getMessage(),
            'exc' => $exc->getCode(),
            'trace' => $exc->getTraceAsString()
        ));
    }

    $client = getTestUser('client');
    \Api\CommonActions\_setAuthorisedUser($client);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array();
    $response = \Api\ClientActions\addTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('title', 'description', 'payment'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task creation: empty post data', $success, $response);

    $_POST = array(
        'title' => '',
        'description' => '',
        'payment' => '',
        'is_active' => ''
    );

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\ClientActions\addTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('title', 'description', 'payment'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task creation: empty values', $success, $response);

    $_POST = array(
        'title' => 'qq',
        'description' => 'qq',
        'payment' => 'qq',
        'is_active' => 'false'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\ClientActions\addTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('payment', 'is_active'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task creation: invalid values', $success, $response);

    $validTask = array(
        'title' => 'Test task',
        'description' => '@testtask. Do not execute',
        'payment' => number_format(MIN_TASK_PAYMENT, 2, '.', ''),
        'is_active' => '1'
    );
    $responseFields = array('id', 'title', 'description', 'payment', 'is_active', '_message');

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validTask;
    $_POST['payment'] /= 2;
    $response = \Api\ClientActions\addTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('payment'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task creation: payment is too low', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validTask;
    $response = \Api\ClientActions\addTask();
    $success = (
        \TestTools\assertHasKeys($response, $responseFields)
        && \TestTools\assertEquals($response['title'], $validTask['title'])
        && \TestTools\assertEquals($response['description'], $validTask['description'])
        && \TestTools\assertEquals($response['payment'], $validTask['payment'])
        && \TestTools\assertEquals($response['is_active'], $validTask['is_active'])
    );
    \TestTools\addTestResult('task creation', $success, $response);
    if (!$success) {
        return false;
    }
    $taskId = $response['id'];

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array();
    $response = \Api\ClientActions\updateTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task update: empty post data', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => '',
        'title' => 'www'
    );
    $response = \Api\ClientActions\updateTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task update: empty id', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => '-1',
        'title' => 'www'
    );
    $response = \Api\ClientActions\updateTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task update: invalid id', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => $taskId,
        'payment' => ''
    );
    $response = \Api\ClientActions\updateTask();
    $success = (
        \TestTools\assertErrorCode(400)
        && \TestTools\assertHasKeys($response, array('_message'))
    );
    \TestTools\addTestResult('task update: no data to update', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => $taskId,
        'title' => $validTask['title'] . 'www',
        'description' => $validTask['description'] . 'www',
        'payment' => '0',
        'is_active' => 'true'
    );
    $response = \Api\ClientActions\updateTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('payment', 'is_active'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task update: invalid field values', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => $taskId,
        'title' => $validTask['title'] . 'www',
        'description' => $validTask['description'] . 'www',
        'payment' => $validTask['payment'] / 2,
        'is_active' => '1'
    );
    $response = \Api\ClientActions\updateTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('payment'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('task update: too low payment', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => $taskId,
        'title' => $validTask['title'] . 'www',
    );
    $response = \Api\ClientActions\updateTask();
    $success = (
       \TestTools\assertHasKeys($response, $responseFields)
        && \TestTools\assertEquals($response['title'], $_POST['title'])
        && \TestTools\assertEquals($response['description'], $validTask['description'])
        && \TestTools\assertEquals($response['payment'], $validTask['payment'])
        && \TestTools\assertEquals($response['is_active'], $validTask['is_active'])
    );
    \TestTools\addTestResult('task update: only title', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => $taskId,
        'title' => $validTask['title'] . 'wwwww',
        'description' => $validTask['description'] . 'www',
        'payment' => number_format(floatval($validTask['payment']) * 2.00, 2, '.', ''),
        'is_active' => '0'
    );
    $response = \Api\ClientActions\updateTask();
    $success = (
       \TestTools\assertHasKeys($response, $responseFields)
        && \TestTools\assertEquals($response['title'], $_POST['title'])
        && \TestTools\assertEquals($response['description'], $_POST['description'])
        && \TestTools\assertEquals(floatval($response['payment']), floatval($_POST['payment']))
        && \TestTools\assertEquals($response['is_active'], '0')
    );
    \TestTools\addTestResult('task update: all fields', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $executor = getTestUser('executor');
    $success = \Db\query('UPDATE `vktask2`.`tasks` ' .
        "SET `executor_id` = {$executor['id']}, `executed_at` = NOW() WHERE `id` = $taskId"
    );
    \TestTools\addTestResult('task update query: set executor_id', $success, \Db\getLastQuery());
    if ($success) {
        \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
        $_POST = array(
            'id' => $taskId,
            'title' => $validTask['title'] . 'wwwqqq',
        );
        $response = \Api\ClientActions\updateTask();
        $success = (
            \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
            && \TestTools\assertHasKeys($response, array('_message'))
        );
        \TestTools\addTestResult('executed task update attempt', $success, $response);

    }
    
    $GLOBALS['__REQUEST_INFO']['isPost'] = false;
    $GLOBALS['__REQUEST_INFO']['isGet'] = true;
    $_POST = array();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = \Api\ClientActions\getTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
    );
    \TestTools\addTestResult("tast get data: no id passed", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('id' => '-1');
    $response = \Api\ClientActions\getTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
    );
    \TestTools\addTestResult("get task data: invalid id passed", $success, $response);

    $responseFields = array(
        'id', 'title', 'description', 'payment', 'client_id',
        'is_active', 'executor_id', 'created_at', 'executed_at'
    );

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('id' => $taskId);
    $response = \Api\ClientActions\getTask();
    $success = (
        \TestTools\assertHasKeys($response, $responseFields)
        && \TestTools\assertEquals($response['id'], $taskId)
        && \TestTools\assertEquals($response['is_active'], '0')
        && \TestTools\assertEquals($response['client_id'], $client['id'])
    );
    \TestTools\addTestResult("get task data", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('id' => $taskId, 'not_executed' => '1');
    $response = \Api\ClientActions\getTask();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('_message'))
    );
    \TestTools\addTestResult("try to get executed task data", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = \Api\ClientActions\tasksList();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], $responseFields, false)
    );
    if ($success) {
        foreach ($response as $task) {
            $success = \TestTools\assertEquals($task['client_id'], $client['id']);
        }
    }
    \TestTools\addTestResult("get client tasks list: without page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '-1');
    $response = \Api\ClientActions\tasksList();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], $responseFields, false)
    );
    \TestTools\addTestResult("get client tasks list: with invalid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '2');
    $response = \Api\ClientActions\tasksList();
    $success = (
        \TestTools\assertEquals(count($response), 0)
        || (
            \TestTools\assertHasKeys($response, array(0), false)
            && \TestTools\assertHasKeys($response[0], $responseFields, false)
        )
    );
    \TestTools\addTestResult("get client tasks list: with valid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = \Api\ClientActions\tasksListInfo();
    $success = (
        \TestTools\assertHasKeys($response, array('total', 'pages', 'items_per_page'))
        && \TestTools\assertEquals($response['total'] > 0, true)
        && \TestTools\assertEquals($response['pages'] > 0, true)
        && \TestTools\assertEquals($response['items_per_page'] > 0, true)
    );
    \TestTools\addTestResult("get client tasks list info", $success, $response);

    \Db\query("DELETE FROM `vktask2`.`tasks` WHERE `description` LIKE '@testtask%'");

    return \TestTools\getTestResults(true);
}

function executorTasksManagementAndDataRetrieving() {
    \TestTools\cleanTestResults();
    \Api\CommonActions\_unsetAuthorisation();

    $executor = getTestUser('executor');
    \Api\CommonActions\_setAuthorisedUser($executor);

    $client = getTestUser('client');
    $pendingTask = array(
        'title' => 'Test task (pending)',
        'description' => '@testtask. Do not execute',
        'payment' => number_format(MIN_TASK_PAYMENT + 0.13, 2, '.', ''),
        'is_active' => '1',
        'client_id' => $client['id']
    );
    $executedTask = $pendingTask + array('executor_id' => $executor['id'], 'title' => 'Test task (executed)');
    $table = '`vktask2`.`tasks`';
    $pendingTask = \Db\insert($pendingTask, $table);
    if (empty($pendingTask)) {
        \TestTools\addTestResult("pending task creation", false, \Db\getLastQuery());
        return \TestTools\getTestResults(true);
    }
    $executedTask = \Db\insert($executedTask, $table);
    if (empty($executedTask)) {
        \TestTools\addTestResult("executed task creation", false, \Db\getLastQuery());
        return \TestTools\getTestResults(true);
    }

    $GLOBALS['__REQUEST_INFO']['isPost'] = false;
    $GLOBALS['__REQUEST_INFO']['isGet'] = true;
    $_POST = array();
    $_GET = array();

    $responseFields = array(
        'id', 'title', 'description', 'client_email', 'executor_id',
        'created_at', 'executed_at', 'paid_to_executor', 'payment'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = \Api\ExecutorActions\pendingTasksList();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], $responseFields, false)
    );
    if ($success) {
        foreach ($response as $task) {
            $success = \TestTools\assertEquals($task['executor_id'], null);
        }
    }
    \TestTools\addTestResult("get pending tasks list: without page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = \Api\ExecutorActions\executedTasksList();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], $responseFields, false)
    );
    if ($success) {
        foreach ($response as $task) {
            $success = \TestTools\assertEquals($task['executor_id'], $executor['id']);
        }
    }
    \TestTools\addTestResult("get executed tasks list: without page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '-1');
    $response = \Api\ExecutorActions\pendingTasksList();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], $responseFields, false)
    );
    \TestTools\addTestResult("get pending tasks list: with invalid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '-1');
    $response = \Api\ExecutorActions\executedTasksList();
    $success = (
        \TestTools\assertHasKeys($response, array(0), false)
        && \TestTools\assertHasKeys($response[0], $responseFields, false)
    );
    \TestTools\addTestResult("get executed tasks list: with invalid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '2');
    $response = \Api\ExecutorActions\pendingTasksList();
    $success = (
        \TestTools\assertEquals(count($response), 0)
        || (
            \TestTools\assertHasKeys($response, array(0), false)
            && \TestTools\assertHasKeys($response[0], $responseFields, false)
        )
    );
    \TestTools\addTestResult("get pending tasks list: with valid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array('page' => '2');
    $response = \Api\ExecutorActions\executedTasksList();
    $success = (
        \TestTools\assertEquals(count($response), 0)
        || (
            \TestTools\assertHasKeys($response, array(0), false)
            && \TestTools\assertHasKeys($response[0], $responseFields, false)
        )
    );
    \TestTools\addTestResult("get executed tasks list: with valid page argument", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = \Api\ExecutorActions\pendingTasksListInfo();
    $success = (
        \TestTools\assertHasKeys($response, array('total', 'pages', 'items_per_page'))
        && \TestTools\assertEquals($response['total'] > 0, true)
        && \TestTools\assertEquals($response['pages'] > 0, true)
        && \TestTools\assertEquals($response['items_per_page'] > 0, true)
    );
    \TestTools\addTestResult("get pending tasks list info", $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_GET = array();
    $response = \Api\ExecutorActions\executedTasksListInfo();
    $success = (
        \TestTools\assertHasKeys($response, array('total', 'pages', 'items_per_page'))
        && \TestTools\assertEquals($response['total'] > 0, true)
        && \TestTools\assertEquals($response['pages'] > 0, true)
        && \TestTools\assertEquals($response['items_per_page'] > 0, true)
    );
    \TestTools\addTestResult("get executed tasks list info", $success, $response);

    $GLOBALS['__REQUEST_INFO']['isPost'] = true;
    $GLOBALS['__REQUEST_INFO']['isGet'] = false;
    $_GET = array();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array();
    $response = \Api\ExecutorActions\executeTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('execute task: empty post data', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => '',
    );
    $response = \Api\ExecutorActions\executeTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('execute task: empty id', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => '-1',
    );
    $response = \Api\ExecutorActions\executeTask();
    $success = (
        \TestTools\assertValidationErrors($response, array('id'))
        && \TestTools\assertHasKeys($response, array('errors', '_message'))
    );
    \TestTools\addTestResult('execute task: invalid id', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => $executedTask['id'],
    );
    $response = \Api\ExecutorActions\executeTask();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_CONFLICT)
        && \TestTools\assertHasKeys($response, array('_message'))
    );
    \TestTools\addTestResult('execute task: already executed', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array(
        'id' => $pendingTask['id'],
    );
    $shouldBePaidToExecutor = floor(floatval($pendingTask['payment']) * (1 - SYSTEM_COMISSION) * 100) / 100;
    $shouldBePaidToExecutor = number_format($shouldBePaidToExecutor, 2, '.', '');
    $shouldBePaidToSystem = number_format(floatval($pendingTask['payment']) - $shouldBePaidToExecutor, 2, '.', '');
    $response = \Api\ExecutorActions\executeTask();
    $responseFields = array('id', 'executor_id', 'executed_at', 'paid_to_executor', 'payment', 'balance', '_message');
    $success = (
       \TestTools\assertHasKeys($response, $responseFields)
        && \TestTools\assertEquals($response['id'], $pendingTask['id'])
        && \TestTools\assertEquals($response['executor_id'], $executor['id'])
        && \TestTools\assertEquals($response['payment'], $pendingTask['payment'])
        && \TestTools\assertEquals(
            number_format(floatval($response['paid_to_executor']), 2, '.', ''),
            $shouldBePaidToExecutor
        )
        && \TestTools\assertEquals(
            number_format(floatval($response['balance']), 2, '.', ''),
            number_format(floatval($executor['balance']) + floatval($shouldBePaidToExecutor), 2, '.', '')
        )
    );
    \TestTools\addTestResult('execute task', $success, $response);

    $rows = \Db\select("SELECT `paid_to_system` FROM $table WHERE `id` = {$pendingTask['id']}");
    $success = (
        \TestTools\assertEquals(empty($rows), false)
        && \TestTools\assertHasKeys($rows, array(0))
        && \TestTools\assertHasKeys($rows[0], array('paid_to_system'))
        && \TestTools\assertEquals(
            number_format(floatval($rows[0]['paid_to_system']), 2, '.', ''),
            $shouldBePaidToSystem
        )
    );
    \TestTools\addTestResult('executed task payment to system', $success, $response);

    \Db\query("DELETE FROM `vktask2`.`tasks` WHERE `description` LIKE '@testtask%'");

    return \TestTools\getTestResults(true);
}

function adminSystemStats() {
    \TestTools\cleanTestResults();
    \Api\CommonActions\_unsetAuthorisation();

    $admin = getTestUser('admin');
    \Api\CommonActions\_setAuthorisedUser($admin);

    $GLOBALS['__REQUEST_INFO']['isPost'] = false;
    $GLOBALS['__REQUEST_INFO']['isGet'] = true;
    $_POST = array();
    $_GET = array();

    $response = \Api\AdminActions\systemStats();
    $success = (
        \TestTools\assertHasKeys($response, array(
            'tasks_total', 'tasks_added_today', 'tasks_added_yesterday', 'tasks_pending_total',
            'tasks_executed_total', 'system_earned_total', 'executors_earned_total', 'tasks_executed_today',
            'system_earned_today', 'executors_earned_today', 'tasks_executed_yesterday',
            'system_earned_yesterday', 'executors_earned_yesterday'
        ))
    );
    \TestTools\addTestResult('system stats', $success, $response);

    return \TestTools\getTestResults(true);
}