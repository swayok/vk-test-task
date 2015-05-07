<?php

class Strings {
    static $strings = array(
        'Enter Login' => 'Введите логин',
        'Enter Password' => 'Введите пароль'
    );

    static public function translate($string) {
        return isset(self::$strings[$string]) ? self::$strings[$string] : $string;
    }
}
