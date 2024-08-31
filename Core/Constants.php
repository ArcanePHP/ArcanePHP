<?php

define(
    'ROOT',
    dirname(__DIR__)
);
define('PUBROOT', ROOT . '/public');
if (isset($_SERVER['REQUEST_SCHEME']) &&  $_SERVER['HTTP_HOST']) {

    define('REQUEST_SCHEME', $_SERVER['REQUEST_SCHEME']);
    define('HOST', $_SERVER['HTTP_HOST']);
    define('ASSET_PATH', REQUEST_SCHEME . '://' . HOST . '/assets/');
}
