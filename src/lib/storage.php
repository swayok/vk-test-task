<?php

namespace Storage;

$__STORAGE = array();

function set($key, $value) {
    $GLOBALS['__STORAGE'][$key] = $value;
}

function get($key) {
    return has($key) ? $GLOBALS['__STORAGE'][$key] : null;
}

function has($key) {
    return array_key_exists($key, $GLOBALS['__STORAGE']);
}

function delete($key) {
    unset($GLOBALS['__STORAGE'][$key]);
}