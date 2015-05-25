<?php

namespace Tests\Api;

require_once __DIR__ . '/../lib/test.tools.php';
require_once __DIR__ . '/../configs/databases.php';
require_once __DIR__ . '/../api/api.controller.php';
require_once __DIR__ . '/../api/api.admin.actions.php';

function getTestsList() {
    return array(
        'Login status & is authorised as role' => __NAMESPACE__ . '\loginStatus',
        'Creating users and Login' => __NAMESPACE__ . '\createUsersAndLogin'
    );
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

function createUsersAndLogin() {
    \TestTools\cleanTestResults();
    \Api\CommonActions\_unsetAuthorisation();
    $GLOBALS['__REQUEST_INFO']['isPost'] = true;
    $GLOBALS['__REQUEST_INFO']['isGet'] = false;

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

    // get admin account to be able create test users
    $admin = \Db\select('SELECT * FROM `vktask1`.`admins` LIMIT 1');
    if (empty($admin)) {
        \TestTools\addTestResult('select admin user', false, 'No admins in DB');
        return \TestTools\getTestResults(true);
    }
    $admin = $admin[0];
    $admin['role'] = 'admin';
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

    // users creation, login, deactivation, get data, get lists and lists info

    // todo: add tests for [get data, get lists and lists info]

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

    \Db\query("DELETE FROM `vktask1`.`{$role}s` WHERE `email` LIKE 'testuser%@test.com'");
    return true;
}