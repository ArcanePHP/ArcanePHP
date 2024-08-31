<?php

namespace Core;

class Config
{
    protected array $config = [];

    const DB_HOST ='localhost';
    const DB_NAME = 'fastkart';
    const  DB_USER = 'root';
    const APP_NAME = 'fastkart';

    const  DB_PASSWORD = '1243';
    const DB_DRIVER = 'mysql';
    const SHOW_ERRORS = true;
    const DEBUG_MODE = true;

    const CACHE = false;

    public function __construct($env)
    {
        // dd($env);
        $this->config =
            [
                'db' =>
                [
                    'DRIVER' => self::DB_HOST,
                    'DB_HOST' => self::DB_HOST,
                    'DB_NAME' => self::DB_NAME],
                    'PASSWORD' => self::DB_PASSWORD,
                    'USER' => 'root'
                ]
            ;
    }
    public  function __get($name)
    {
        return $this->config[$name] ?? null;
    }
}
