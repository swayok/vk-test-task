<?php

namespace Utils;

const HTTP_CODE_OK = 200;

const HTTP_CODE_INVALID = 400;
const HTTP_CODE_UNAUTHORIZED = 401;
const HTTP_CODE_FORBIDDEN = 403;
const HTTP_CODE_NOT_FOUND = 404;
const HTTP_CODE_NOT_ALLOWED = 405;
const HTTP_CODE_CONFLICT = 409;
const HTTP_CODE_INTERNAL_SERVER_ERRORR = 500;

function setHttpCode($httpCode) {
    if (is_numeric($httpCode)) {
        http_response_code($httpCode);
    } else {
        throw new \Exception('Invalid HTTP Code: ' . $httpCode);
    }
}

function terminate($httpCode, array $response = array()) {
    setHttpCode($httpCode);
    if (!empty($response)) {
        echo json_encode($response);
    }
    exit;
}

function hashPassword($password) {
    return hash('sha256', SALT . $password);
}

/**
 * @param $data
 * @param array $validators = array('key' => array(options)); for list of options - see $defaults
 * @return array - assotiative array of errors.
 */
function validateData(array &$data, array $validators) {
    $defaults = array(
        'required' => false,
        'type' => null,         //< 'int|float|bool|email'
        'regexp' => null,
        'convert' => true,      //< converts value to 'type'
        'min_length' => 0,
        'max_length' => 0,
        'messages' => array(
            'required' => \Dictionary\translate('Value should not be empty'),
            'type' => \Dictionary\translate('Value data type is invalid'),
            'regexp' => \Dictionary\translate('Value does not match pattern'),
            'min_length' => \Dictionary\translate('Value is too short'),
            'max_length' => \Dictionary\translate('Value is too long')
        )
    );
    $errors = array();
    foreach ($validators as $key => $validator) {
        $validator = array_replace_recursive($defaults, $validator);
        if ($validator['required'] && (!array_key_exists($key, $data) || isEmptyValue($data[$key]))) {
            $errors[$key] = $validator['messages']['required'];
        } else if (array_key_exists($key, $data)) {
            if (
                !empty($validator['type'])
                && !isValidValueType($data[$key], $validator['type'], $validator['convert'])
            ) {
                $errors[$key] = $validator['messages']['type'];
            } else if (
                $validator['min_length'] > 0
                && (
                    !is_string($data[$key])
                    || mb_strlen($data[$key]) < $validator['min_length']
                )
            ) {
                $errors[$key] = $validator['messages']['min_length'];
            } else if (
                $validator['max_length'] > 0
                && (
                    !is_string($data[$key])
                    || mb_strlen($data[$key]) > $validator['max_length']
                )
            ) {
                $errors[$key] = $validator['messages']['max_length'];
            } else if (
                !empty($validator['regexp'])
                && (
                    (!is_string($data[$key]) && !is_numeric($data[$key]))
                    || !preg_match($validator['regexp'], "{$data[$key]}")
                )
            ) {
                $errors[$key] = $validator['messages']['regexp'];
            }
        }
    }
    return $errors;
}

function isEmptyValue($value) {
    return empty($value) && !is_bool($value) && !is_numeric($value);
}

function isValidValueType(&$value, $type, $convert = false) {
    switch (strtolower($type)) {
        case 'integer':
        case 'int':
            if (is_int($value) || (is_string($value) && preg_match('%^\d+$%', $value))) {
                if ($convert) {
                    $value = intval($value);
                }
                return true;
            }
            return false;
        case 'id':
            if (isValidValueType($value, 'int', true) && $value > 0) {
                return true;
            }
            return false;
        case 'float':
            if (is_float($value) || is_int($value) || (is_string($value) && preg_match('%^\d+(\.\d+)?$%', $value))) {
                if ($convert) {
                    $value = floatval($value);
                }
                return true;
            }
            return false;
        case 'bool':
        case 'boolean':
            if (is_bool($value) || (is_numeric($value) && ("$value" === '0' || "$value" === '1'))) {
                if (!is_bool($value) && $convert) {
                    $value = !!intval($value);
                }
                return true;
            }
            return false;
        case 'email':
            $emailRegexp = "%^[a-z0-9!#\$\%&'*+/=?\^_`{|}~-]+(?:\.[a-z0-9!#\$\%&'*+/=?\^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$%is";
            return is_string($value) && preg_match($emailRegexp, $value);
        default:
            return true;
    }
}