<?php
//
declare(strict_types=1);
use Core\Cache;
use Core\Application;
use Core\Config;
use Core\Router;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require dirname(__DIR__) . '/Core/Init.php';
require_once ROOT . '/vendor/autoload.php';
//

$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


$ctrl = 'controller';
$ac = 'action';
$app = new Application(new Config($_ENV));


// $router = new Router();
$url = $_SERVER['QUERY_STRING'];
// if ($_ENV['DEBUG']&& $_ENV['cache']) {
if (Config::CACHE && Config::DEBUG_MODE) {

} else {
    $router = Router::getInstance();
    // dd($router);
    include ROOT . '/Routes/Web.php';
    // dd($GLOBALS['routes']);

    $router::dispatch($url);
}

// vd($router);

// $url = $_SERVER['QUERY_STRING'];
// Router::dispatch($url);
// vd(Router::class);
// $router->dispatch($url);
// $router_match_result =  $router->matched($url);
