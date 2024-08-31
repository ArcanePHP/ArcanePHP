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
if (!isset($_SERVER['REQUEST_SCHEME'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('REQUEST_SCHEME', $scheme);
    define('HOST', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
    define('ASSET_PATH', REQUEST_SCHEME . '://' . HOST . '/assets/');

    // dd(HOST);
}

