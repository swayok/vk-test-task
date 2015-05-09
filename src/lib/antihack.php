<?php

$hackRegexp = '%(<\?php|cgi-bin|php://|\?>|wget\s+|file_get_contents|system\s*\(|chmod\s+\(|chmod\s+-r|chmod\s+-\d\d\d|sys_get_temp_dir|suhosin|echo\(|echo\s+[\(\'"`])%is';
if (!empty($_GET) && preg_match($hackRegexp, urldecode($_SERVER['QUERY_STRING']), $ret)) {
    hackDetected();
}
if (!empty($_POST) && preg_match($hackRegexp, print_r($_POST, true), $ret)) {
    hackDetected();
}
if (!empty($_SERVER['HTTP_USER_AGENT']) && preg_match($hackRegexp, $_SERVER['HTTP_USER_AGENT'], $ret)) {
    hackDetected();
}
$badUriRegexp = '%(^(/admin)?/app($|/)|/cgi_)%is';
if (!empty($_SERVER['REQUEST_URI']) && preg_match($badUriRegexp, $_SERVER['REQUEST_URI'], $ret)) {
    hackDetected();
}

function hackDetected() {
    \Utils\setHttpCode(\Utils\HTTP_CODE_FORBIDDEN);
    echo 'Большой брат наблюдает за тобой! >_<';
    exit;
}