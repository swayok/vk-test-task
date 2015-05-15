<?php

namespace Dictionary;

$__TRANSLATIONS = array(
    'Authorisation' => 'Авторизация',
    'Password' => 'Пароль',
    'Role' => 'Роль',
    'Client' => 'Заказчик',
    'Executor' => 'Исполниитель',
    'Admin' => 'Администратор',
    'Log-In' => 'Войти',
    'Enter e-mail' => 'Введите E-mail адрес',
    'Invalid e-mail' => 'Значение не является E-mail адресом',
    'Enter password' => 'Введите пароль',
    'Select role' => 'Выберите роль',
    'Form contains invalid data' => 'Форма содержит недопустимые данные',
    'Authorisation error: user not found' => 'Ошибка авторизации: пользователь не найден',
    'Value not found' => 'Значение не найдено',
    'Invalid value' => 'Недопустимое значение',
    'Failed to save data to DB' => 'Не удалось сохранить данные в базу данных',
    'No data passed' => 'Нет данных',
    'ID is required' => 'Требуется ID',
    'Record with passed ID was not found in DB' => 'Запись с требуемым ID не найдена в базе данных',
);

function translate($string) {
    return array_key_exists($string, $GLOBALS['__TRANSLATIONS'])
        ? $GLOBALS['__TRANSLATIONS'][$string]
        : $string;
}
