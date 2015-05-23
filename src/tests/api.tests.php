<?php

namespace Tests\Api;

require_once __DIR__ . '/../lib/test.tools.php';
require_once __DIR__ . '/../configs/databases.php';
require_once __DIR__ . '/../api/api.controller.php';
require_once __DIR__ . '/../api/api.admin.actions.php';

function getTestsList() {
    return array(
//        'Login status & is authorised as role' => __NAMESPACE__ . '\loginStatus',
        'Creating users and Login' => __NAMESPACE__ . '\createUsersAndLogin'
    );
}

function loginStatus() {
    \TestTools\cleanTestResults();

    // noone logged in

    unset($_SESSION['admin'], $_SESSION['client'], $_SESSION['executor']);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    \TestTools\addTestResult('no users logged in', $success, $response);

    $_SESSION['client'] = array();
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    \TestTools\addTestResult('no users logged in / empty client data', $success, $response);

    $response = \Api\CommonActions\_isAuthorisedAs('client');
    $success = (
        \TestTools\assertEquals($response, false)
    );
    \TestTools\addTestResult('is authorised as client: false', $success, $response);
    unset($_SESSION['client']);

    $_SESSION['executor'] = array();
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    \TestTools\addTestResult('no users logged in / empty executor data', $success, $response);

    $response = \Api\CommonActions\_isAuthorisedAs('executor');
    $success = (
        \TestTools\assertEquals($response, false)
    );
    \TestTools\addTestResult('is authorised as executor: false', $success, $response);
    unset($_SESSION['executor']);

    $_SESSION['admin'] = array();
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    \TestTools\addTestResult('no users logged in / empty admin data', $success, $response);
    unset($_SESSION['admin']);

    $response = \Api\CommonActions\_isAuthorisedAs('admin');
    $success = (
        \TestTools\assertEquals($response, false)
    );
    \TestTools\addTestResult('is authorised as admin: false', $success, $response);

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
        '_route' => 'add-task'
    );
    $executor = array(
        'id' => '1',
        'email' => 'executor@test.ru',
        'role' => 'executor',
        '_route' => 'tasks-list',
        'balance' => 111.11
    );

    $_SESSION['admin'] = $admin;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($admin))
        && \TestTools\assertEquals($response['email'], $admin['email'])
        && \TestTools\assertEquals($response['role'], $admin['role'])
    );
    \TestTools\addTestResult('only admin logged in', $success, $response);

    $_SESSION['executor'] = $executor;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($executor))
        && \TestTools\assertEquals($response['email'], $executor['email'])
        && \TestTools\assertEquals($response['role'], $executor['role'])
        && \TestTools\assertEquals($response['balance'], $executor['balance'])
    );
    \TestTools\addTestResult('admin and executor logged in', $success, $response);

    $_SESSION['client'] = $client;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    \TestTools\addTestResult('admin, executor and client logged in', $success, $response);

    $response = \Api\CommonActions\_isAuthorisedAs('client');
    $success = (
        \TestTools\assertEquals($response, true)
    );
    \TestTools\addTestResult('is authorised as client: true', $success, $response);

    $response = \Api\CommonActions\_isAuthorisedAs('executor');
    $success = (
        \TestTools\assertEquals($response, true)
    );
    \TestTools\addTestResult('is authorised as executor: true', $success, $response);

    $response = \Api\CommonActions\_isAuthorisedAs('admin');
    $success = (
        \TestTools\assertEquals($response, true)
    );
    \TestTools\addTestResult('is authorised as admin: true', $success, $response);

    unset($_SESSION['executor']);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    \TestTools\addTestResult('admin and client logged in', $success, $response);

    unset($_SESSION['admin']);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    \TestTools\addTestResult('only client logged in', $success, $response);

    unset($_SESSION['client']);
    $_SESSION['executor'] = $executor;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($executor))
        && \TestTools\assertEquals($response['email'], $executor['email'])
        && \TestTools\assertEquals($response['role'], $executor['role'])
        && \TestTools\assertEquals($response['balance'], $executor['balance'])
    );
    \TestTools\addTestResult('only executor logged in', $success, $response);

    try {
        $response = \Api\CommonActions\_isAuthorisedAs('qq');
        \TestTools\addTestResult('is authorised as qqq: exception', false, 'Fail. Exception should be thrown!');
    } catch (\Exception $exc) {
        \TestTools\addTestResult('is authorised as qqq: exception', true, $response);
    }

    return \TestTools\getTestResults(true);
}

function createUsersAndLogin() {
    // todo: add duplicate email test for add user actions
    \TestTools\cleanTestResults();
    unset($_SESSION['admin'], $_SESSION['client'], $_SESSION['executor']);
    $GLOBALS['__REQUEST_INFO']['isPost'] = true;
    $GLOBALS['__REQUEST_INFO']['isGet'] = false;

    try {
        $response = \Api\AdminActions\addAdmin();
        \TestTools\addTestResult('admin not logged in: exception', false, $response);
    } catch (\Exception $exc) {
        $success = (
            \TestTools\assertEquals($exc->getCode(), 401)
            && \TestTools\assertHasKeys(json_decode($exc->getMessage(), true), array('message', 'route'))
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
    $_SESSION['admin'] = $admin;
    $validUser = array(
        'email' => 'testuser' . time() . '@test.com',
        'password' => 'l9DFhc1cXHSot4OkxZj1',
    );

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array();
    $response = \Api\AdminActions\addAdmin();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('user creation: empty post data', $success, $response);

    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password', 'role'))
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
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('user creation: empty values', $success, $response);

    $_POST = array(
        'email' => '',
        'password' => '',
        'role' => ''
    );
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password', 'role'))
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
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'is_active'))
    );
    \TestTools\addTestResult('user creation: invalid values', $success, $response);

    $_POST = array(
        'email' => 'qq',
        'password' => 'qq',
        'role' => 'qq'
    );
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'role'))
    );
    \TestTools\addTestResult('login: invalid values', $success, $response);

    // client creation, login, deactivation

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addClient();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    \TestTools\addTestResult('client creation', $success, $response);
    if (!$success) {
        return \TestTools\getTestResults(true);
    }
    $clientId = $response['id'];

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'executor';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('client login: wrong role', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST['role'] = 'client';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', '_route', 'role'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $clientId)
    );
    \TestTools\addTestResult('client login', $success, $response);
    $_SESSION['admin'] = $admin;

    $_POST = array();
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateClient();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('id'))
    );
    \TestTools\addTestResult('client update: no data', $success, $response);

    $_POST = array(
        'id' => '',
        'is_active' => '',
        'password' => ''
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateClient();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('id'))
    );
    \TestTools\addTestResult('client update: empty values', $success, $response);

    $_POST = array(
        'id' => 'qq',
        'is_active' => 'qq',
        'password' => ''
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateClient();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('id', 'is_active'))
    );
    \TestTools\addTestResult('client update: invalid values', $success, $response);

    $notExistsingId = intval(\Db\selectValue('SELECT MAX(`id`) FROM `vktask1`.`clients`')) + 90000;
    $_POST = array(
        'id' => $notExistsingId,
        'is_active' => '0',
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateClient();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('id'))
    );
    \TestTools\addTestResult('client update: invalid id', $success, $response);

    $_POST = array(
        'id' => $clientId,
        'is_active' => '0'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateClient();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $clientId)
        && \TestTools\assertEquals($response['is_active'], '0')
    );
    \TestTools\addTestResult('client deactivation', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'client';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('deactivated client login', $success, $response);
    $_SESSION['admin'] = $admin;

    //\Db\query('DELETE FROM `vktask1`.`clients` WHERE `email` LIKE "testuser%@test.com"');

    // executor creation, login, deactivation

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addExecutor();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    \TestTools\addTestResult('executor creation', $success, $response);
    if (!$success) {
        return \TestTools\getTestResults(true);
    }
    $executorId = $response['id'];

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'admin';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('executor login: wrong role', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST['role'] = 'executor';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', '_route', 'role'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $executorId)
    );
    \TestTools\addTestResult('executor login', $success, $response);
    $_SESSION['admin'] = $admin;

    $_POST = array(
        'id' => $executorId,
        'is_active' => '0'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateExecutor();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $executorId)
        && \TestTools\assertEquals($response['is_active'], '0')
    );
    \TestTools\addTestResult('executor deactivation', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'executor';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('deactivated executor login', $success, $response);
    $_SESSION['admin'] = $admin;

    //\Db\query('DELETE FROM `vktask1`.`executors` WHERE `email` LIKE "testuser%@test.com"');

    // admin creation, login, deactivation

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addAdmin();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    \TestTools\addTestResult('admin creation', $success, $response);
    if (!$success) {
        return \TestTools\getTestResults(true);
    }
    $adminId = $response['id'];

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'client';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('admin login: wrong role', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST['role'] = 'admin';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', '_route', 'role'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
    );
    \TestTools\addTestResult('admin login', $success, $response);
    $_SESSION['admin'] = $admin;

    $_POST = array(
        'id' => $adminId,
        'is_active' => '0'
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateAdmin();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['id'], $adminId)
        && \TestTools\assertEquals($response['is_active'], '0')
    );
    \TestTools\addTestResult('admin deactivation', $success, $response);

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'admin';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    \TestTools\addTestResult('deactivated admin login', $success, $response);
    $_SESSION['admin'] = $admin;

    //\Db\query('DELETE FROM `vktask1`.`admins` WHERE `email` LIKE "testuser%@test.com"');

    return \TestTools\getTestResults(true);
}