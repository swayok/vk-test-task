<?php
require_once __DIR__ . '/../src/configs/bootstrap.php';
require_once __DIR__ . '/../src/lib/test.tools.php';
require_once __DIR__ . '/../src/lib/debug.php';
require_once __DIR__ . '/../src/tests/libs.tests.php';
require_once __DIR__ . '/../src/tests/api.tests.php';
?>
<html>
<head>
    <title>Tests</title>
</head>
<body>
    <h1>Tests</h1>

    <h2>Utils</h2>
    <?php
        /*
        foreach (\Tests\Utils\getTestsList() as $testTitle => $function) {
            echo '<h3>' . $testTitle . '</h3>';
            \TestTools\prepareForTest();
            $testResults = $function();
            echo '<dl>';
            foreach ($testResults as $subTestTitle => $result) {
                echo '<dt>' . $subTestTitle . '</dt>';
                echo '<dd>';
                if (is_array($result)) {
                    echo dprToStr($result);
                } else {
                    echo $result;
                }
                echo '</dd>';
            }
            echo '</dl>';
        }*/
    ?>

    <h2>Api</h2>
    <?php
        foreach (\Tests\Api\getTestsList() as $testTitle => $function) {
            echo '<h3>' . $testTitle . '</h3>';
            \TestTools\prepareForTest();
            $testResults = $function();
            echo '<dl>';
            foreach ($testResults as $subTestTitle => $result) {
                echo '<dt>' . $subTestTitle . '</dt>';
                echo '<dd>';
                if (is_array($result)) {
                    echo dprToStr($result);
                } else {
                    echo $result;
                }
                echo '</dd>';
            }
            echo '</dl>';
        }
    ?>
</body>
</html>
