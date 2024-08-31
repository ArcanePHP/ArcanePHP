<?php

use Core\Cache;
use Core\Config;
use Core\StaticRouter;
use Core\View;
use Twig\Profiler\Dumper\TextDumper;

use function PHPSTORM_META\elementType;

function vd(...$st)
{
  foreach ($st as $st) {
    echo '<pre>';
    var_dump($st);
    echo '</pre>';
  }
}


function ph(string $html)
{
  echo htmlspecialchars($html);
}

function dd(...$str)
{
  if (isset($_SERVER['REQUEST_METHOD'])) {

    foreach ($str as $ar) {
      echo '<pre>';
      if (is_array($ar)) {
        var_dump($ar);
      } else {
        if (is_string($ar)) {
          var_dump(htmlspecialchars($ar));
        } else {
          var_dump($ar);
        }
      }
      echo '</pre>';
    }
    die();
  } else {
    foreach ($str as $ar) {
      // var_dump($ar);
      var_export($ar) . PHP_EOL;
    }
    die();
  }
}
function pr($st): void
{
  echo '<pre>';
  (print_r($st));
  echo '</pre>';
}
function env(string $key): string
{
  return $_ENV[$key] ?? '';
}
function addglobal(\Twig\Environment $twig, array $globals): void
{
  foreach ($globals as $key => $value) {
    $twig->addGlobal($key, $value);
  }
}



function users(string $session_key): array|string|bool
{
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }

  if (!isset($_SESSION)) {
    return false;
  }

  if (array_key_exists($session_key, $_SESSION)) {
    return $_SESSION[$session_key];
  }

  return false;
}
function returnArrayWith($array, $key, $checkfor)
{

  return array_filter($array, function ($element) use ($checkfor, $key) {

    if ($element[$key] == $checkfor) {
      $filteredArray = ($element);
      vd($element);
      return $element[$key];
    }
  });
}

function checkForGenuineID(
  array $data,
  string $arraykey,
  string $id
): array|bool {
  foreach ($data as $key => $value) {
    foreach ($value as $key2 => $value2) {
      if ($key2 == $arraykey && $value2 == $id) {
        return $value;
      } else {
        return false;
      }
    }
  }
  return false;
}
function getArrayWithSpecificKey(string $key, array $array): array
{
  // vd($array);
  $filteredArray = [];

  foreach ($array as $element) {
    if (array_key_exists($key, $array)) {
      $filteredArray[$key] = $array[$key];
    }
  }

  return $filteredArray;
}
function generateReferenceId(array $allReferenceIds): int
{
  $randomNumber = mt_rand(10000000, 99999999);
  foreach ($allReferenceIds as $key => $value) {

    if ($randomNumber == $value) {
      generateReferenceId($allReferenceIds);
    }
  }

  return $randomNumber;
}

function splitArrayIntoChunks($array, int $userid, array $allReferenceIds)
{

  $addionalDetail = [];
  if (array_key_exists('additional-details', $array)) {
    $addionalDetail = $array['additional-details'];
    unset($array['additional-details']);
  }

  $chunkSize = 5;  // Number of key-value pairs per chunk
  -$chunks = array_chunk($array, $chunkSize, true);  // Preserve keys
  $pattern = '/-(\d)$/';
  foreach ($chunks as $key => $value) {
    if ($key == 0) {
      $chunks[$key]['user_id'] = $userid;
      $chunks[$key]['reference_id'] = generateReferenceId($allReferenceIds);
      if ($addionalDetail) {
        $chunks[$key]['additional-details'] = $addionalDetail;
      }

      continue;
    } else {
      $data = removeStuff($pattern, $value);
      $data['user_id'] = $userid;
      $data['reference_id'] = generateReferenceId($allReferenceIds);
      if ($addionalDetail) {
        $data['additional-details'] = $addionalDetail;
      }

      $chunks[$key] = $data;
    }
  }


  return $chunks;
}

function removeStuff(string $pattern, array $originalArray)
{
  $modifiedArray = [];
  foreach ($originalArray as $key => $value) {
    $modifiedKey = preg_replace($pattern, '', $key);
    $modifiedArray[$modifiedKey] = $value;
  }
  return $modifiedArray;
}

function claimsPerUser(array $allClaims)
{
  // Initialize an empty result array
  $resultArray = [];

  // Group entries by user_id
  foreach ($allClaims as $claim) {
    $user_id = $claim["user_id"];
    // unset($claim["user_id"]); // Remove user_id from the entry
    $resultArray[$user_id][] = $claim;
  }
  return $resultArray;
}

class Show
{

  public static array $showable;
  public function __construct(array $showable)
  {
    self::$showable = $showable;
  }

  public function on($route, string|null $file = '')
  {


    $renderable = '';
    if (!$file) {
      $methods = explode('_', $route);
      // return false;
      // dd($methods);
      // dd(array_values($GLOBALS['routes']));
      // $routes;
      if (key_exists('url_stack', $GLOBALS)) {
        $routes = $GLOBALS['url_stack'][$route];
        // vd('hi');
        // dd($routes);


      } else {

        $ff = StaticRouter::getRoutesWithName();
        $route = $ff[$route]['view'];
        // dd($route);
        // $routes = array_merge(...array_values($GLOBALS['routes']));
        // dd($routes);
        // $GLOBALS['url_stack'] = $routes;
        // dd($routes[$route]);
      }
      // dd($routes);

      // exit;
      $renderable = $route . '.html';
      // dd($renderable);

    } else {
      $renderable = $file . '.html';
    }


    View::render($renderable, ['showable' => self::$showable]);

    // dd($url);
  }

  public function errorsOn($route, string|null $file = '')
  {

    $renderable = '';
    if (!$file) {

      $methods = explode('_', $route);
      $url = $GLOBALS['routes'][$methods[0]][$route];
      $renderable = $methods[0] . '/' . $methods[1] . '.html';
    } else {
      $renderable = $file . '.html';
    }
    foreach (self::$showable as $key => $value) {
      if ($value) {
        self::$showable[$key] = '<p class="error-message  text-danger my-4 fade">' . $value . '</p>';
      }
    }
    return View::render($renderable, self::$showable);
  }
}


function show(array $showable)
{
  return new Show($showable);
}


function redirect(string $route)
{

  $methods = explode('_', $route);
  // dd($GLOBALS['routes']);
  $routeWithName = StaticRouter::getRoutesWithName();
  // dd($routeWithName['u]);
  $url = $routeWithName[$route]['url'];
  // dd($url);
  if (isset($routeWithName[$route]['url'])) {
    $url = $routeWithName[$route]['url'];
    // dd($url);
  } else {
    dd("route name '{$route}' not defined");
  };

  // dd($url);
  return View::redirect($url);
}

function router()
{
  return StaticRouter::getInstance();
}

function ajax($params)
{
  $event = $params['event'];
  $eventOn = $params['event-on'];
  $uniqueIdFrom = $params['unique-id-from'];
  $url = $params['url'];
  $method = $params['method'];
  $multiple = $params['multiple'];

  $url = str_replace('{id}', "", $url);
  // Parse unique-id-from
  list($closest, $attribute) = explode('->', $uniqueIdFrom);
  list($selectorType, $selectorValue) = explode('::', $closest);
  $selectorValue = trim($selectorValue, '.');

  $js = "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                function ajaxCall(id) {
                    $.ajax({
                        url: '$url' + id,
                        type: '$method',
                        data: {
                            'id': id
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                        },
                        success: function(response) {
                            console.log(response);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error(textStatus, errorThrown);
                        }
                    });
                }
    ";

  if ($multiple) {
    $js .= "
                let elements = document.querySelectorAll('$eventOn');
                elements.forEach(element => {
                    element.addEventListener('$event', function(e) {
                        let id = e.target.closest('.$selectorValue').getAttribute('$attribute');
                        ajaxCall(id);
                    });
                });
        ";
  } else {
    $js .= "
                let element = document.querySelector('$eventOn');
                element.addEventListener('$event', function(e) {
                    let id = e.target.closest('.$selectorValue').getAttribute('$attribute');
                    ajaxCall(id);
                });
        ";
  }

  $js .= "});
        </script>";
  // dd($js);
  return $js;
}


function getUrl(string $key)
{
  $routeWithName = StaticRouter::getRoutesWithName();
  // dd($routeWithName['u]);
  $url = $routeWithName[$key]['url'];
  return $url;
  // return $GLOBALS['routes'][Config::APP_NAME][$key];
}


function removeCache(string $key)
{

  $cacheInstance = new Cache();
  $cache = Cache::get();
  $cache_instance = $cache->getItem($key);

  if (is_null($cache_instance->get())) {
    dd('cache not found');
  } else {
    $cache_instance->set(null);
    $res = $cache->save($cache_instance);
    dd('Removed cache');

    // return $item;
  }

  // function getCache



}
function getCacheItem(string $key)
{

  $cacheInstance = Cache::getInstance();
  $cacheInstance::get()->getItem($key);
  return $cacheInstance->get() ?? false;
  // $cache_instance = self::$cache->getItem($key);

}

function cacheInstance()
{
  return Cache::get();
}

function setCacheItem($item)
{
  $cache = cacheInstance();
  $cache_instance = $cache->getItem($item);
  // if (!is_null($cache_instance)) {
  removeCache($item);
  $cache_instance->set($item);
  return $cache->save($cache_instance);
  // }
  // vd(StaticRouter::getInstance());
}
