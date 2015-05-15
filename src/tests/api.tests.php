<?php

namespace Tests\Api;

require_once __DIR__ . '/../lib/test.tools.php';
require_once __DIR__ . '/../configs/databases.php';
require_once __DIR__ . '/../api/api.controller.php';
require_once __DIR__ . '/../api/api.admin.actions.php';

function getTestsList() {
    return array(
//        'Login status & is authorised as role' => __NAMESPACE__ . '\loginStatus',
        'Creating users and Login' => __NAMESPACE__ . '\createUsersAndlogin'
    );
}

function loginStatus() {
    $results = array();

    // noone logged in

    unset($_SESSION['admin'], $_SESSION['client'], $_SESSION['executor']);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    $results['no users logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $_SESSION['client'] = array();
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    $results['no users logged in / empty client data'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $response = \Api\CommonActions\_isAuthorisedAs('client');
    $success = (
        \TestTools\assertEquals($response, false)
    );
    $results['is authorised as client: false'] = $success ? 'ok' : \TestTools\getLastTestDetails();
    unset($_SESSION['client']);

    $_SESSION['executor'] = array();
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    $results['no users logged in / empty executor data'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $response = \Api\CommonActions\_isAuthorisedAs('executor');
    $success = (
        \TestTools\assertEquals($response, false)
    );
    $results['is authorised as executor: false'] = $success ? 'ok' : \TestTools\getLastTestDetails();
    unset($_SESSION['executor']);

    $_SESSION['admin'] = array();
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    $results['no users logged in / empty admin data'] = $success ? 'ok' : \TestTools\getLastTestDetails();
    unset($_SESSION['admin']);

    $response = \Api\CommonActions\_isAuthorisedAs('admin');
    $success = (
        \TestTools\assertEquals($response, false)
    );
    $results['is authorised as admin: false'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // someone logged in

    $admin = array(
        'id' => '1',
        'email' => 'admin@test.ru',
        'role' => 'admin',
        'route' => 'admin-dashboard'
    );
    $client = array(
        'id' => '1',
        'email' => 'client@test.ru',
        'role' => 'client',
        'route' => 'add-task'
    );
    $executor = array(
        'id' => '1',
        'email' => 'executor@test.ru',
        'role' => 'executor',
        'route' => 'tasks-list',
        'balance' => 111.11
    );

    $_SESSION['admin'] = $admin;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($admin))
        && \TestTools\assertEquals($response['email'], $admin['email'])
        && \TestTools\assertEquals($response['role'], $admin['role'])
    );
    $results['only admin logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $_SESSION['executor'] = $executor;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($executor))
        && \TestTools\assertEquals($response['email'], $executor['email'])
        && \TestTools\assertEquals($response['role'], $executor['role'])
        && \TestTools\assertEquals($response['balance'], $executor['balance'])
    );
    $results['admin and executor logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $_SESSION['client'] = $client;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    $results['admin, executor and client logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $response = \Api\CommonActions\_isAuthorisedAs('client');
    $success = (
        \TestTools\assertEquals($response, true)
    );
    $results['is authorised as client: true'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $response = \Api\CommonActions\_isAuthorisedAs('executor');
    $success = (
        \TestTools\assertEquals($response, true)
    );
    $results['is authorised as executor: true'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $response = \Api\CommonActions\_isAuthorisedAs('admin');
    $success = (
        \TestTools\assertEquals($response, true)
    );
    $results['is authorised as admin: true'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    unset($_SESSION['executor']);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    $results['admin and client logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    unset($_SESSION['admin']);
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    $results['only client logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    unset($_SESSION['client']);
    $_SESSION['executor'] = $executor;
    $response = \Api\CommonActions\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($executor))
        && \TestTools\assertEquals($response['email'], $executor['email'])
        && \TestTools\assertEquals($response['role'], $executor['role'])
        && \TestTools\assertEquals($response['balance'], $executor['balance'])
    );
    $results['only executor logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    try {
        $response = \Api\CommonActions\_isAuthorisedAs('qq');
        $results['is authorised as qqq: exception'] = 'Fail. Exception should be thrown!';
    } catch (\Exception $exc) {
        $results['is authorised as qqq: exception'] = 'ok';
    }

    return $results;
}

function createUsersAndlogin() {
    $results = array();
    unset($_SESSION['admin'], $_SESSION['client'], $_SESSION['executor']);

    $response = \Api\AdminActions\addAdmin();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('route'))
    );
    $results['admin not logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // get admin account to be able create test users
    $admin = \Db\select('SELECT * FROM `vktask1`.`admins` LIMIT 1');
    if (empty($admin)) {
        $results['Select admin user'] = 'No admins in DB';
        return $results;
    }
    $admin = $admin[0];
    $admin['role'] = 'admin';
    $_SESSION['admin'] = $admin;
    $validUser = array(
        'email' => 'testuser' . time() . '@test.com',
        'password' => 'l9DFhc1cXHSot4OkxZj1',
    );

    $GLOBALS['__REQUEST_INFO']['isPost'] = true;
    $GLOBALS['__REQUEST_INFO']['isGet'] = false;

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = array();
    $response = \Api\AdminActions\addAdmin();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    $results['user creation: empty post data'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password', 'role'))
    );
    $results['login: empty post data'] = $success ? 'ok' : \TestTools\getLastTestDetails();

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
    $results['user creation: empty values'] = $success ? 'ok' : \TestTools\getLastTestDetails();

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
    $results['login: empty values'] = $success ? 'ok' : \TestTools\getLastTestDetails();

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
    $results['user creation: invalid values'] = $success ? 'ok' : \TestTools\getLastTestDetails();

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
    $results['login: invalid values'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // client creation, login, deactivation

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addClient();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    $results['client creation'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'executor';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    $results['client login: wrong role'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST['role'] = 'client';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'route', 'role'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
    );
    $results['client login'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $_POST = array();
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateClient();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_INVALID)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('id'))
    );
    $results['client update: no data'] = $success ? 'ok' : \TestTools\getLastTestDetails();

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
    $results['client update: empty values'] = $success ? 'ok' : \TestTools\getLastTestDetails();

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
    $results['client update: invalid values'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $_POST = array(
        'id' => '1',
        'is_active' => '0',
        'password' => ''
    );
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\updateClient();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('id'))
    );
    $results['client update: invalid id'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // todo: deactivation

    \Db\query('DELETE FROM `vktask1`.`clients` WHERE `email` LIKE "testuser%@test.com"');

    // executor creation, login, deactivation

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addExecutor();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    $results['executor creation'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'admin';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    $results['executor login: wrong role'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST['role'] = 'executor';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'route', 'role'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
    );
    $results['executor login'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // todo: deactivation

    \Db\query('DELETE FROM `vktask1`.`executors` WHERE `email` LIKE "testuser%@test.com"');

    // admin creation, login, deactivation

    $_POST = $validUser;
    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $response = \Api\AdminActions\addAdmin();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'is_active'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
        && \TestTools\assertEquals($response['is_active'], '1')
    );
    $results['admin creation'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST = $validUser;
    $_POST['role'] = 'client';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_NOT_FOUND)
        && \TestTools\assertHasKeys($response, array('errors', 'message'))
        && \TestTools\assertHasKeys($response['errors'], array('email', 'password'))
    );
    $results['admin login: wrong role'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    \Utils\setHttpCode(\Utils\HTTP_CODE_OK);
    $_POST['role'] = 'admin';
    $response = \Api\CommonActions\login();
    $success = (
        \TestTools\assertHasKeys($response, array('id', 'email', 'route', 'role'))
        && \TestTools\assertEquals($response['email'], $validUser['email'])
    );
    $results['admin login'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // todo: deactivation

    \Db\query('DELETE FROM `vktask1`.`admins` WHERE `email` LIKE "testuser%@test.com"');

    return $results;
}

/*function _createTestUser($role) {
    $data = array(
        'email' => $role . time() . '@test.com',
        'password' => ,
        'is_active' => 1
    );
}*/