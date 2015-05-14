<?php

namespace Tests\Api;

require_once __DIR__ . '/../lib/test.tools.php';
require_once __DIR__ . '/../api.actions.php';

function getTestsList() {
    return array(
        'Login Status' => __NAMESPACE__ . '\loginStatus',
        'Login' => __NAMESPACE__ . '\login'
    );
}

function loginStatus() {
    $results = array();

    unset($_SESSION['admin'], $_SESSION['client'], $_SESSION['executor']);
    $response = \Api\loginStatus();
    $success = (
        \TestTools\assertErrorCode(\Utils\HTTP_CODE_UNAUTHORIZED)
        && \TestTools\assertHasKeys($response, array('message'))
    );
    $results['no users logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $admin = array(
        'email' => 'admin@test.ru',
        'role' => 'admin',
        'route' => 'admin-dashboard'
    );
    $client = array(
        'email' => 'client@test.ru',
        'role' => 'client',
        'route' => 'add-task'
    );
    $executor = array(
        'email' => 'executor@test.ru',
        'role' => 'executor',
        'route' => 'tasks-list',
        'balance' => 111.11
    );

    $_SESSION['admin'] = $admin;
    $response = \Api\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($admin))
        && \TestTools\assertEquals($response['email'], $admin['email'])
        && \TestTools\assertEquals($response['role'], $admin['role'])
    );
    $results['only admin logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $_SESSION['executor'] = $executor;
    $response = \Api\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($executor))
        && \TestTools\assertEquals($response['email'], $executor['email'])
        && \TestTools\assertEquals($response['role'], $executor['role'])
        && \TestTools\assertEquals($response['balance'], $executor['balance'])
    );
    $results['admin and executor logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    $_SESSION['client'] = $client;
    $response = \Api\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    $results['admin, executor and client logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    unset($_SESSION['executor']);
    $response = \Api\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    $results['admin and client logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    unset($_SESSION['admin']);
    $response = \Api\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($client))
        && \TestTools\assertEquals($response['email'], $client['email'])
        && \TestTools\assertEquals($response['role'], $client['role'])
    );
    $results['only client logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    unset($_SESSION['client']);
    $_SESSION['executor'] = $executor;
    $response = \Api\loginStatus();
    $success = (
        \TestTools\assertHasKeys($response, array_keys($executor))
        && \TestTools\assertEquals($response['email'], $executor['email'])
        && \TestTools\assertEquals($response['role'], $executor['role'])
        && \TestTools\assertEquals($response['balance'], $executor['balance'])
    );
    $results['only executor logged in'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    return $results;
}

function login() {
    $results = array();




    return $results;
}