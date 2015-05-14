<?php

namespace Tests\Api;

require_once __DIR__ . '/../lib/test.tools.php';
require_once __DIR__ . '/../api/api.controller.php';

function getTestsList() {
    return array(
        'Login status & is authorised as role' => __NAMESPACE__ . '\loginStatus',
        'Login' => __NAMESPACE__ . '\login'
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

function login() {
    $results = array();




    return $results;
}