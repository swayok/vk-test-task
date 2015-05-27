<?php

define('APP_INITIATED', true);
define('SYSTEM_COMISSION', 0.05); //< 1 = 100%
define('MIN_TASK_PAYMENT', 10.00); //< RUB
define('SALT', 'Qw3D0dpODPc2wfrBP1x6ri9M40H7SAJS');
define('DATA_GRID_ITEMS_PER_PAGE', 15);
define('ENVIRONMENT', !empty($_SERVER['OS']) && stristr($_SERVER['OS'], 'Win') || !empty($_SERVER['WINDIR']) ? 'dev' : 'production');
define('VIEWS_HTTP_CACHE_DURATION', 300);