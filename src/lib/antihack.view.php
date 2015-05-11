<?php

require_once 'utils.php';
require_once 'request.php';

if (!\Request\isGet() || !\Request\isAjax()) {
    \Utils\setHttpCode(\Utils\HTTP_CODE_FORBIDDEN);
    echo 'Forbidden';
    exit;
}

require_once 'antihack.php';