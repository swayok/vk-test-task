<?php

namespace Tests\Utils;

require_once __DIR__ . '/../lib/test.tools.php';
require_once __DIR__ . '/../lib/utils.php';

function getTestsList() {
    return array(
        'Set HTTP code' => __NAMESPACE__ . '\utilsSetHttpCode',
        'Data Validation' => __NAMESPACE__ . '\utilsDataValidation'
    );
}

function utilsSetHttpCode() {
    \Utils\setHttpCode(401);
    return array(
        'HTTP 401' => \TestTools\assertErrorCode(401) ? 'ok' : \TestTools\getLastTestDetails()
    );
}

function utilsDataValidation() {
    $results = array();

    // test 'required' validator
    $data = array(
        'a' => '',
        'b' => null,
        'c' => false,
        'd' => 0,
        'e' => '0',
        'f' => '0.00',
        'g' => array(),
        'h' => 123.45,
        'i' => 'false',
        'j' => '123.65',
        'k' => 200,
        'l' => '200,11',
        'm' => true,
        'n' => 1,
        'o' => '1',
        'p' => 'true',
    );
    $validator = array('required' => true, 'convert' => false);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $success = (
        \TestTools\assertHasKeys($errors, array('a', 'b', 'g'))
    );
    $results['validator: required'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test int data type
    $validator = array('type' => 'int', 'convert' => false);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $validKeys = array('d', 'e', 'k', 'n', 'o');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($data), $validKeys))
    );
    $results['validator: type = int'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test float data type
    $validator = array('type' => 'float', 'convert' => false);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $validKeys = array('d', 'e', 'f', 'h', 'j', 'k', 'n', 'o');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($data), $validKeys))
    );
    $results['validator: type = float'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test bool data type
    $validator = array('type' => 'bool', 'convert' => false);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $validKeys = array('c', 'd', 'e', 'm', 'n', 'o');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($data), $validKeys))
    );
    $results['validator: type = bool'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test email data type
    $emails = array(
        'a' => 'qq',
        'b' => null,
        'c' => '1',
        'd' => 2,
        'e' => 'qq@',
        'f' => '@',
        'g' => '@qq',
        'h' => '@qq.qq',
        'i' => 'qq@qq',
        'j' => 'qq@qq.qq',
        'k' => 'q8q@q8q.qq',
        'l' => 'qq.qq@',
        'm' => 'qq.qq@qq',
        'n' => 'qq.qq@qq.qq',
        'o' => 'qq_qq@qq.qq',
        'p' => 'qq%qq@qq.qq',
        'q' => 'qq$qq@qq.qq',
        'r' => 'qq*qq@qq.qq',
        's' => 'qq_qq@qq.qq',
        't' => 'qq-qq@qq.qq',
        'u' => '_qq@qq.qq',
        'v' => 'qq_@qq.qq',
    );
    $validator = array('type' => 'email', 'convert' => false);
    $errors = \Utils\validateData($emails, array_fill_keys(array_keys($emails), $validator));
    $validKeys = array('j', 'k', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($emails), $validKeys))
    );
    $results['validator: type = email'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test regexp
    $validator = array('regexp' => '%5$%', 'convert' => false);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $validKeys = array('j', 'h');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($data), $validKeys))
    );
    $results['validator: regexp'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test min length
    $validator = array('min_length' => '4', 'convert' => false);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $validKeys = array('f', 'i', 'j', 'l', 'p');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($data), $validKeys))
    );
    $results['validator: min_length'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test min length
    $validator = array('max_length' => '4', 'convert' => false);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $validKeys = array('a', 'e', 'f', 'o', 'p');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($data), $validKeys))
    );
    $results['validator: max_length'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // test int data type (should be last because 'convert' fill be forced to true and corrupt values in $data)
    $validator = array('type' => 'id', 'convert' => true);
    $errors = \Utils\validateData($data, array_fill_keys(array_keys($data), $validator));
    $validKeys = array('k', 'n', 'o');
    $success = (
        \TestTools\assertHasNoKeys($errors, $validKeys)
        && \TestTools\assertHasKeys($errors, array_diff(array_keys($data), $validKeys))
    );
    $results['validator: type = id'] = $success ? 'ok' : \TestTools\getLastTestDetails();

    // todo: test remove_if_empty and default options

    return $results;
}