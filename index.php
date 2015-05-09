<?php
require_once __DIR__ . '/src/configs/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Система заказов</title>

    <link rel="stylesheet" href="/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div id="page-content">

    </div>

    <script src="/js/jquery/jquery-2.1.4.min.js"></script>
    <script src="/css/bootstrap/js/bootstrap.min.js"></script>
    <script src="/js/app.js"></script>
    <script>
        App.init(<?php echo json_encode($_GET); ?>);
    </script>
</body>
</html>