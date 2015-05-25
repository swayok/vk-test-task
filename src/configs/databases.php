<?php

require_once __DIR__ . '/../lib/db.php';

if (ENVIRONMENT === 'dev') {
    \Db\addDbConnectionConfig(array(
        'user' => 'vktask1us',
        'password' => '123123'
    ));
} else {
    require_once 'prod.databases.php';
}
