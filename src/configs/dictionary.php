<?php

namespace Dictionary;

$__TRANSLATIONS = array(
    'Enter Login' => 'Введите логин',
    'Enter Password' => 'Введите пароль'
);

function translate($string) {
    return array_key_exists($string, $GLOBALS['__TRANSLATIONS'])
        ? $GLOBALS['__TRANSLATIONS'][$string]
        : $string;
}
