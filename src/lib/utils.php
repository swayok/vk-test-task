<?php

namespace Utils;

const HTTP_CODE_OK = 200;

const HTTP_CODE_INVALID = 400;
const HTTP_CODE_UNAUTHORIZED = 401;
const HTTP_CODE_FORBIDDEN = 403;
const HTTP_CODE_NOT_FOUND = 404;
const HTTP_CODE_NOT_ALLOWED = 405;
const HTTP_CODE_CONFLICT = 409;

function setHttpCode ($httpCode) {
    if (is_numeric($httpCode)) {
        http_response_code($httpCode);
    } else {
        throw new \Exception('Invalid HTTP Code: ' . $httpCode);
    }
}
