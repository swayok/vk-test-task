<?php
require_once __DIR__ . '/../src/configs/bootstrap.php';
require_once __DIR__ . '/../src/api/api.controller.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="favicon.ico" />

    <title><?php echo \Dictionary\translate('Order execution system'); ?></title>

    <link rel="stylesheet" href="/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="container" id="page-container">
        <div id="page-content">

        </div>
    </div>

    <script src="/js/jquery/jquery-2.1.4.min.js"></script>
    <script src="/js/doT.min.js"></script>
    <script src="/css/bootstrap/js/bootstrap.min.js"></script>
    <script src="/js/utils.js"></script>
    <script src="/js/app.configs.js"></script>
    <script src="/js/app.components.js"></script>
    <script src="/js/app.controllers.js"></script>
    <script src="/js/app.js"></script>
    <script>
        App.init(
            <?php echo json_encode($_GET, JSON_UNESCAPED_UNICODE); ?>,
            <?php echo json_encode(\Api\Controller\runAction('status'), JSON_UNESCAPED_UNICODE); ?>
        );
    </script>
</body>
</html>