<?php

namespace Core;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Drivers\Files\Config as CacheConfig;


class Cache
{
    public static ExtendedCacheItemPoolInterface $cache;
    private static $instance;
    public function __construct()
    {

        $cf = new ConfigurationOption([
            "path" => ROOT . '/../Cache',
        ]);

        CacheManager::setDefaultConfig($cf);
        self::$cache = CacheManager::getInstance('Files', $cf);
    }

    public static function get()
    {
        return self::$cache;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getItem($cache_key)
    {
        CacheManager::setDefaultConfig(new ConfigurationOption([
            "path" => ROOT . '/../Cache',
        ]));
        $cache = CacheManager::getInstance('files');
        $cachedRoutes = $cache->getItem($cache_key);
        if (!$cachedRoutes->isHit()) {
            return 'item not found';
        }
        return $cachedRoutes->get();    }
}
