<?php

namespace Dictionary;

$__TRANSLATIONS = array(
    'Access denied' => 'Доступ запрещен',
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
    'Order execution system' => 'Система выполнения заказов',
    'OES' => 'СВЗ',
    'System management' => 'Управление системой',
    'Toggle navigation' => 'Показать/скрыть меню',
    'Dashboard' => 'Состояние системы',
    'Clients' => 'Клиенты',
    'Executors' => 'Исполнители',
    'Admins' => 'Администраторы',
    'Rows' => 'Строки',
    'From' => 'из',
    'Newer' => 'Новее',
    'Older' => 'Старее',
    'Edit' => 'Редактировать',
    'Activate' => 'Активировать',
    'Deactivate' => 'Деактивировать',
    'Status' => 'Состояние',
    'Created at' => 'Дата создания',
    'Created by' => 'Создатель',
    'Actions' => 'Действия',
    'Active' => 'Активен',
    'Inactive' => 'Не активен',
);

function translate($string) {
    return array_key_exists($string, $GLOBALS['__TRANSLATIONS'])
        ? $GLOBALS['__TRANSLATIONS'][$string]
        : $string;
}
