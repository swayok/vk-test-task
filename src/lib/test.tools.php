<?php

namespace TestTools;

require_once 'db.php';

$__LAST_TEST_DETAILS = array();
$__TEST_RESULTS = array();

function getLastTestDetails($response = null) {
    return $GLOBALS['__LAST_TEST_DETAILS'] + array('response' => $response);
}

function cleanTestResults() {
    $GLOBALS['__TEST_RESULTS'] = array();
}

function addTestResult($title, $success, $response = null) {
    if (isset($GLOBALS['__TEST_RESULTS'][$title])) {
        throw new \Exception("Duplicate test called [{$title}]");
    }
    $GLOBALS['__TEST_RESULTS'][$title] = $success ? 'ok' : getLastTestDetails($response);
}

function getTestResults($cleanResults = false) {
    $ret = $GLOBALS['__TEST_RESULTS'];
    if ($cleanResults) {
        cleanTestResults();
    }
    return $ret;
}

function prepareForTest() {
    http_response_code(200);
}

function assertEquals($receivedValue, $expectedValue) {
    $GLOBALS['__LAST_TEST_DETAILS'] = array();
    if ($receivedValue === $expectedValue) {
        $GLOBALS['__LAST_TEST_DETAILS'] = array('success' => true);
        return true;
    } else {
        $GLOBALS['__LAST_TEST_DETAILS'] = array(
            'success' => false,
            'message' => 'Values are not equal',
            'details' => array(
                'received' => $receivedValue,
                'expected' => $expectedValue,
                'db_query' => \Db\getLastQuery()
            )
        );
        return false;
    }
}

function assertNotEquals($receivedValue, $expectedValue) {
    $GLOBALS['__LAST_TEST_DETAILS'] = array();
    if ($receivedValue === $expectedValue) {
        $GLOBALS['__LAST_TEST_DETAILS'] = array(
            'success' => false,
            'message' => 'Values are equal while they shouldn\'t',
            'details' => array(
                'received' => $receivedValue,
                'expected' => $expectedValue,
                'db_query' => \Db\getLastQuery()
            )
        );
        return false;
    } else {
        $GLOBALS['__LAST_TEST_DETAILS'] = array('success' => true);
        return true;
    }
}

function assertHasKeys($receivedArray, $testKeys, $onlyThisKeys = true) {
    $GLOBALS['__LAST_TEST_DETAILS'] = array();
    $flip = array_flip($testKeys);
    if (!is_array($receivedArray)) {
        $GLOBALS['__LAST_TEST_DETAILS'] = array(
            'success' => false,
            'message' => 'Response is not an array',
            'db_query' => \Db\getLastQuery()
        );
        return false;
    }
    $intersect = array_intersect_key($receivedArray, $flip);
    if (count($intersect) !== count($flip)) {
        $GLOBALS['__LAST_TEST_DETAILS'] = array(
            'success' => false,
            'message' => 'Some expected keys not found',
            'details' => array(
                'received_array' => $receivedArray,
                'expected_keys' => $testKeys,
                'db_query' => \Db\getLastQuery()
            )
        );
        return false;
    }
    if ($onlyThisKeys) {
        $diff = array_diff_key($receivedArray, $flip);
        if (!empty($diff)) {
            $GLOBALS['__LAST_TEST_DETAILS'] = array(
                'success' => false,
                'message' => 'Received more data then expected',
                'details' => array(
                    'received_array' => $receivedArray,
                    'expected_keys' => $testKeys,
                    'unexpected_data' => $diff,
                    'db_query' => \Db\getLastQuery()
                )
            );
            return false;
        }
    }
    $GLOBALS['__LAST_TEST_DETAILS'] = array('success' => true);
    return true;
}

function assertHasNoKeys($receivedArray, $testKeys) {
    $GLOBALS['__LAST_TEST_DETAILS'] = array();
    $flip = array_flip($testKeys);
    if (!is_array($receivedArray)) {
        $GLOBALS['__LAST_TEST_DETAILS'] = array(
            'success' => false,
            'message' => 'Response is not an array',
            'db_query' => \Db\getLastQuery()
        );
        return false;
    }
    $intersect = array_intersect_key($receivedArray, $flip);
//    dpr($intersect, $receivedArray, $flip);
    if (count($intersect) > 0) {
        $GLOBALS['__LAST_TEST_DETAILS'] = array(
            'success' => false,
            'message' => 'Some not expected keys were found in response',
            'details' => array(
                'received_array' => $receivedArray,
                'not_expected_keys' => $testKeys,
                'db_query' => \Db\getLastQuery()
            )
        );
        return false;
    }
    $GLOBALS['__LAST_TEST_DETAILS'] = array('success' => true);
    return true;
}

function assertValidationErrors($receivedData, $invalidFields = array()) {
    $GLOBALS['__LAST_TEST_DETAILS'] = array();
    $success = (
        assertErrorCode(401)
        && assertHasKeys($receivedData, array('errors'), false)
        && assertHasKeys($receivedData['errors'], array_keys($invalidFields))
    );
    if ($success) {
        foreach ($invalidFields as $fieldName => $expectedError) {
            $success = assertEquals($receivedData['errors'][$fieldName], $expectedError);
            if (!$success) {
                break;
            }
        }
    }
    return $success;
}

function assertErrorCode($expectedCode = null) {
    $GLOBALS['__LAST_TEST_DETAILS'] = array(
        'success' => (!$expectedCode && http_response_code() >= 400) || http_response_code() === intval($expectedCode),
        'message' => 'Unexpected HTTP Code',
        'details' => array(
            'expected_http_code' => $expectedCode,
            'http_code' => http_response_code(),
            'db_query' => \Db\getLastQuery()
        )
    );
    return $GLOBALS['__LAST_TEST_DETAILS']['success'];
}