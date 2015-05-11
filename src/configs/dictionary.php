<?php

namespace Dictionary;

$__TRANSLATIONS = array(
    'Authorisation' => 'Авторизация',
    'Password' => 'Пароль',
    'Role' => 'Роль',
    'Client' => 'Заказчик',
    'Executor' => 'Исполниитель',
    'Admin' => 'Админимстратор',
    'Log-In' => 'Войти',
    'Enter email' => 'Введите Email-адрес',
    'Enter password' => 'Введите пароль',
    'Select role' => 'Выберите роль',
    'Form contains invalid data' => 'Форма содержит недопустимые данные'
);

function translate($string) {
    return array_key_exists($string, $GLOBALS['__TRANSLATIONS'])
        ? $GLOBALS['__TRANSLATIONS'][$string]
        : $string;
}
