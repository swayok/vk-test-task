<?php

namespace ErrorReporter;

// Set new error handler
set_error_handler('\ErrorReporter\error_to_exception', error_reporting());
set_exception_handler('\ErrorReporter\exception_handler');
register_shutdown_function('\ErrorReporter\shutdown_handler');

function fix_objects_for_dump(&$data, $printObjectClassOnly = false) {
    try {
        if (is_array($data)) {
            foreach ($data as &$value) {
                if (is_array($value) || is_object($value)) {
                    fix_objects_for_dump($value, $printObjectClassOnly);
                }
            }
        } else if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else if (method_exists($data, '__toString')) {
                $data = $data->__toString();
            } else if ($printObjectClassOnly) {
                $data = 'Object of class {' . get_class($data) . '}';
            }
        }
        if (is_string($data) && strlen($data) > 1000000) {
            $data = substr($data, 0, 1000000) . ' ...';
        }
    } catch (\Exception $exc) {
        $data = 'Exception happened in fix_objects_for_dump: ' . $exc->getMessage();
    }
    return $data;
}

if (!defined('DEBUG')) {
    define('DEBUG', false);
}

class PhpErrorException extends \ErrorException {

};

function error_to_exception($errno, $errstr, $errfile, $errline) {
    $exc = new PhpErrorException($errstr, 0, $errno, $errfile, $errline);
    throw $exc;
}

/**
 * @param \Exception $exception
 * @param bool $exit
 * @param bool $silent - exception info won't be printed
 */
function exception_handler(\Exception $exception, $exit = true, $silent = false) {
    error_handler(
        $exception->getCode(),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        null,
        $exception->getTrace(),
        $exception,
        $exit,
        $silent
    );
}

function shutdown_handler() {
    $error = error_get_last();
    if (is_array($error)) {
        error_handler($error['type'], $error['message'], $error['file'], $error['line'], null, null, $error);
    }
};

function error_handler($code, $message, $file, $line, $context = null, $trace = null, $object = null, $exit = true, $silent = false) {
    $ignoreErrors = array(
        'constant.*already\s*defined',
        'Headers\s*already\s*sent',
        'Premature\s*end\s*of\s*JPEG\s*file',
        'recoverable\s*error:\s*Corrupt\s*JPEG\s*data',
        'terminating\s*connection\s*due\s*to\s*administrator\s*command'
    );
    if (preg_match('%' . implode('|', $ignoreErrors) . '%i', $message)) {
        return;
    }
    $doNotShow = $silent;
    if (!$doNotShow && preg_match('%password\s*authentication\s*failed%is', $message)) {
        $doNotShow = true;
    }
    if (empty($trace)) {
        $trace = debug_backtrace(false);
    }
    foreach ($trace as $info) {
        if (!empty($info['file']) && $info['file'] == __FILE__ || stristr($info['function'], 'ErrorReporter\\')) {
            array_shift($trace);
        } else {
            break;
        }
    }
    if (!count($trace)) {
        $trace = preg_replace('%^.*Stack trace:(.*)$%is', '$1', $message);
        $trace = preg_replace('%\s*thrown$%is', '$1', $trace);
    }
    $message = preg_replace('%Stack trace:.*$%is', '', $message);
    $message = preg_replace(
        '%Uncaught exception\s*\'(.*?)\'.*?message\s*\'(.*?)\'\s*in.*$%is',
        '<b>$1:</b> $2',
        $message
    );
    if (preg_match('%(unlink|mkdir|chmod|copy|rmdir)\(%is', $message)) {
        // skip exit on error of file unlink
        $exit = false;
        $doNotShow = true;
    }
    $errorPlace = 'In ' . $file . ' on line ' . $line ;

    switch ($code) {
        case 1:
        case 16:
            $header = 'PHP Error';
            break;
        case 2:
        case 32:
            $header = 'PHP Warning';
            break;
        case 8:
            $header = 'PHP Notice';
            break;

        case 0:
            $header = 'Uncaught Exception';
            break;
        default:
            $header = 'Error';
    }
    // Clean output buffer
    while (ob_get_level() !== 0) {
        ob_end_clean();
    }
    $error404Patterns = array(
        'Controller.*?((has\s*no\s*action)|(not\s*exists))',
        'page not found'
    );
    $isDebug = DEBUG || (defined('FORCE_DEBUG') && FORCE_DEBUG);
    if (preg_match('%' . implode('|', $error404Patterns) . '%is', $message)) {
        $httpCode = '404';
    } else {
        $httpCode = '500';
    }
    // set header with http code
    if (!$doNotShow) {
        if ($httpCode == '404') {
            //$fileName = '404.html';
            if (!headers_sent()) {
                header('HTTP/1.0 404 Not Found', true, 404);
            }
        } else if ($httpCode === '418') {
            //$fileName = 'hack_attempt.html';
            if (!headers_sent()) {
                header("HTTP/1.0 500 Internal Server Error", true, 500);
            }
        } else {
            //$fileName = 'error.html';
            if (!headers_sent()) {
                header("HTTP/1.0 500 Internal Server Error", true, 500);
            }
        }
    }
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        // request via ajax
        echo json_encode(array(
            'error' => $header,
            'message' => $message,
            'place' => $errorPlace,
            'trace' => $trace
        ));
        if ($exit) {
            exit;
        } else {
            return;
        }
    }

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <title><?php echo $header ?></title>
        <style type="text/css" media="screen">
            html {
                margin: 0;
                padding: 0;
            }

            body {
                width: 90%;
                margin: 0 auto;
                padding: 30px 5%;
                font-size: 14px;
                font-family: Verdana, sans-serif;
                background-color: #fffafa;
                overflow: auto !important;
            }

            body * {
                overflow: visible !important;
            }

            h1, h2 {
                color: #639cb1;
                border-bottom: 1px solid #d7e1e1;
                text-transform: uppercase;
                font-family: Arial, sans-serif;
                font-weight: 100;
                letter-spacing: 2px;
            }

            h1 {
                font-size: 22px;
                padding: 0.159em 0;
                margin: 0.159em 0;
            }

            h2 {
                font-size: 14px;
                padding: 0.25em 0;
                margin: 0.25em 0;
            }

            .backtrace, .backtrace li {
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .line, .path {
                font-family: monospace;
                color: #a1061f;
            }

            .backtrace .path {
                display: block;
                font-size: 14px;
                margin-top: 1em;
                padding: 0 0 0.25em;
                color: #7f7a79;
            }

            .code-block {
                padding-left: 3.5em;
                border: 1px solid #b0a8a6;
                border-width: 1px 0;
                font-size: 12px;
                font-family: monospace;
                line-height: 1.4;
                background: #ddd;
            }

            .code-block .line {
                float: left;
                width: 3em;
                margin-left: -3.375em;
                padding: 0.25em 0;
                text-align: right;
            }

            .code-block code {
                display: block;
                background-color: #eee;
                padding: 0.25em;
            }
        </style>
        <!--[if IE ]>
        <style type="text/css" media="screen">
            html {
                background: #BEC6C6;
            }
        </style>
        <![endif]-->
    </head>
    <body>
    <h1><?php echo $header ?></h1>

    <p><?php echo $message ?></p>
    <p>In <span class="path"><?php echo $file ?></span> on line <span class="line"><?php echo $line ?></span>
    </p>
        <?php if (!empty($trace)): ?>
            <h2>Stack backtrace</h2>
            <ol class="backtrace">
            <?php if (is_array($trace)): ?>
                <?php foreach ($trace as $line): ?>
                    <?php if (isset($line['function']) && in_array($line['function'], array('trigger_error'))) {
                        continue;
                    } ?>
                    <li>
                        <span class="path"><?php echo isset($line['file']) ? $line['file'] . ':' : 'PHP inner process:' ?></span>

                        <div class="code-block">
                            <?php
                            echo '<span class="line">' . (isset($line['line']) ? $line['line'] . '.' : '') . '</span>';

                            $function = isset($line['function']) ? $line['function'] : '';

                            $code = '<?php' . (isset($line['class']) ? $line['class'] : '') . (isset($line['type']) ? $line['type'] : '') . $function;

                            if (isset($line['args'])) {
                                $args = array();

                                foreach ($line['args'] as $arg) {
                                    fix_objects_for_dump($arg, true);
                                    if (is_string($arg)) {
                                        $args[] = '"' . $arg . '"';
                                    } else if (is_array($arg)) {
                                        $args[] = print_r($arg, true);
                                    } else if (is_bool($arg)) {
                                        $args[] = $arg === true ? 'true' : 'false';
                                    } else {
                                        $args[] = $arg;
                                    }
                                }
                                $code .= '(' . implode(', ', $args) . '); ?>';
                            }

                            $code = highlight_string($code, true);
                            $code = str_replace(array('&lt;?php', '?&gt;'), '', $code);

                            echo $code;
                            ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="code-block"><?php echo preg_replace('%#%', "<br>#", $trace); ?><br><br></div>
            <?php endif ?>
            </ol>
        <?php endif ?>
        <hr>
        <h2>POST</h2>
        <div class="code-block">
            <?php highlight_string(print_r($_POST, true)); ?>
        </div>
        <h2>GET</h2>
        <div class="code-block">
            <?php highlight_string(print_r($_GET, true)); ?>
        </div>
        <h2>FILES</h2>
        <div class="code-block">
            <?php highlight_string(print_r($_FILES, true)); ?>
        </div>
        <h2>SERVER</h2>
        <div class="code-block">
            <?php highlight_string(print_r($_SERVER, true)); ?>
        </div>
    </body>
    </html>
    <?php
    $report = ob_get_contents();
    ob_end_clean();
    if (!$doNotShow) {
        if ($isDebug) {
            // display report
            echo $report;
        } else {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
//                    $filePath = ROOT . DS . 'View' . DS . $fileName;
//                    if (file_exists($filePath) && !is_dir($filePath)) {
//                        echo file_get_contents($filePath);
//                    }
        }
    }
    if ($exit) {
        exit;
    }
}