<?php

namespace Core;

class Helpers
{

    public static function getTableName(string $calledClass): string
    {
        $modelName = substr(strrchr($calledClass, '\\'), 1);
        $tableName = strtolower(str_replace('Model', '', $modelName)) ?: $modelName;
        return $tableName;
    }


    public static function calledMethod(): string
    {

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1];
        return $caller['function'];
    }
public static function dd(...$str)
{

  foreach ($str as $ar) {
    echo '<pre>';
    var_dump($ar);
    echo '</pre>';
  }
  die();
}

}