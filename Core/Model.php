<?php

namespace Core;

use PDO;
use PDOException;
use Phpfastcache\Core\Item\ExtendedCacheItemInterface;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;

class Model
{
    protected static DB $db;
    protected static string $arg;
    protected static string $calledClass;
    protected static ExtendedCacheItemPoolInterface $cache;

    protected static array $ajax;
    public function __construct()
    {
        self::$db = Application::db();
        // dd('heyy');
    }


    public static function get(int $id)
    {
        // Get the database connection
        self::$db = Application::db();

        // Determine the table name
        $tableName = self::whichModel();

        // Get table information, including the primary key
        $tableInfo = self::getTableInfo($tableName);
        $primaryKey = $tableInfo['primary_key'];

        // Construct the SQL query, directly including the table name
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $primaryKey . ' = :id';

        try {
            // Prepare the statement
            $stmt = self::$db->prepare($sql);

            // Bind the ID value
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Execute the query
            $stmt->execute();

            // Fetch the result as an associative array
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if any data was found
            if ($data === false) {
                return null; // or return an empty array, depending on your preference
            }

            return $data;
        } catch (PDOException $e) {
            // Handle the exception (log it, rethrow it, etc.)
            // Example: log the error and return null
            error_log($e->getLine() . 'Database query error: ' . $e->getMessage());
            return null;
        } finally {
            // Ensure the statement is closed
            if (isset($stmt)) {
                $stmt->closeCursor();
            }
        }
    }

    public static function all()
    {

        // dd(self::whichModel());
        self::$db = Application::db();
        $tableName = self::whichModel();
        $sql = 'SELECT * FROM ' . $tableName;
        // dd($sql);
        $stmt = self::$db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['tablename'] = $tableName;
        // dd($data);
        return $data;
    }

    public static function whichModel()
    {
        $calledClass = static::class;
        $modelName = substr(strrchr($calledClass, '\\'), 1);
        $tableName = strtolower(str_replace('Model', '', $modelName)) ?: $modelName;
        return $tableName;
    }
    public static function delete(int|array $data)
    {
        if (is_int($data)) {
            $tableName = self::whichModel();
            $user_id = users('user_id');
            // $sql = "delete * from $tableName where  user_id = $user_id and  $tableName" . "_id" . " = $data";
            $id = $data;
            $data = [
                'user_id' => $user_id,
                "{$tableName}_id" => $id
            ];
            $conditions = array_map(fn($col) => "`$col` = :$col", array_keys($data));
            // dd($conditions);
            // vd($data);
            $query = "DELETE FROM `$tableName` WHERE " . implode(' AND ', $conditions);
            // dd($query);
            self::$db = Application::db();
            $stmt = self::$db->prepare($query);
            // dd($stmt->execute($data));
            return $stmt->execute($data);

            // dd($sql);
        }
    }
    public static function create(array $data, string $arg = '')
    {

        $userid = users('user_id');
        $tableName = self::whichModel();
        $tableInfo = self::getTableInfo($tableName);
        self::$arg = $arg;
        $data['user_id'] = $userid;
        $preparedData = self::prepareData($data, $tableInfo ?? []);
        // dd($preparedData);
        return self::insertData($tableName, $preparedData);
    }
    protected static function prepareData($data, $tableInfo)
    {
        $preparedData = [];
        foreach ($data as $key => $value) {
            if (isset($tableInfo[$key])) {
                $columnInfo = $tableInfo[$key];
                if ($value === null && !$columnInfo['nullable'] && $columnInfo['default'] !== null) {
                    $value = $columnInfo['default'];
                }
                $preparedData[$key] = $value;
            } else {
                error_log("model.php,line 140:Column $key does not exist in the table.");
            }
        }
        return $preparedData;
    }


    protected static function insertData($tableName, $data)
    {
        self::$db = Application::db();
        try {
            self::$db->beginTransaction();

            if (self::$arg == 'ifExist::remove') {
                $res = self::removeExistingRecord($tableName, $data);
                // vd($res);
                if ($res > 0) {
                    self::$db->commit();
                    return 'item removed from :' . $tableName;
                }
            }
            $columns = array_keys($data);

            $placeholders = array_map(fn($col) => ":$col", $columns);
            $query = "INSERT INTO `$tableName` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";

            // dd($query);
            $stmt = self::$db->prepare($query);
            // dd($data);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            // vd($stmt);

            $result = $stmt->execute() ? self::$db->lastInsertId() : false;

            self::$db->commit();

            return $result;
        } catch (\PDOException $e) {
            self::$db->rollBack();
            echo ($e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }
    private static function removeExistingRecord($tableName, $data)
    {
        try {
            // self::$db = Application::db();
            $conditions = array_map(fn($col) => "`$col` = :$col", array_keys($data));
            // vd('data',$data);
            $query = "DELETE FROM `$tableName` WHERE " . implode(' AND ', $conditions);
            // dd($data);

            $stmt = self::$db->prepare($query);
            // vd($stmt);
            // $stmt->execute([':tableName' => $tableName]);
            // $modified_array[':asdf'] = 'fasd';
            foreach ($data as $key => $value) {
                $res = $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            echo ($e->getLine() . $e->getMessage());
            return false;
        }
    }
    public static function getTableInfo($tableName)
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {

            $cacheInstance = Cache::getInstance();
            self::$cache = $cacheInstance::get();
            $key = $tableName . '_table_info';

            $cacheItem = self::$cache->getItem($key);

            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }
        self::$db = Application::db();
        try {
            $query = "SELECT 
                c.COLUMN_NAME, c.DATA_TYPE, c.IS_NULLABLE, c.COLUMN_DEFAULT, c.EXTRA,
                k.REFERENCED_TABLE_NAME,
                CASE WHEN tc.CONSTRAINT_TYPE = 'PRIMARY KEY' THEN 'YES' ELSE 'NO' END AS IS_PRIMARY_KEY
                FROM 
                INFORMATION_SCHEMA.COLUMNS c
                LEFT JOIN 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
                ON 
                c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME
                LEFT JOIN
                INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
                ON
                k.CONSTRAINT_NAME = tc.CONSTRAINT_NAME AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
                WHERE 
                c.TABLE_NAME = :tableName";
            $stmt = self::$db->prepare($query);
            $stmt->execute([':tableName' => $tableName]);
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            // dd($tableName);
            // dd($columns);
            if (empty($columns)) {
                // echo ("No columns found for table: $tableName");
                return false;
            }

            $tableInfo = [];
            $tableInfo['columns'] = [];

            foreach ($columns as $column) {
                if (isset($tableInfo[$column['COLUMN_NAME']])) {
                    continue;
                }
                $tableInfo[$column['COLUMN_NAME']] = [
                    'type' => $column['DATA_TYPE'],
                    'nullable' => ($column['IS_NULLABLE'] === 'YES'),
                    'default' => $column['COLUMN_DEFAULT'],
                    'extra' => $column['EXTRA'],
                    'belongsToTable' => $column['REFERENCED_TABLE_NAME']
                    // 'isPrimaryKey' => ($column['IS_PRIMARY_KEY'] === 'YES'),
                ];
                if ($column['IS_PRIMARY_KEY'] == 'YES') {
                    $tableInfo['primary_key'] = $column['COLUMN_NAME'];
                }
                // if()
                // vd(
                // $column['COLUMN_NAME']
                // );
                array_push($tableInfo['columns'], $column['COLUMN_NAME']);
                // dd($tableInfo);
            }
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $cacheItem->set($tableInfo);
                $cacheItem->expiresAfter(3500); // Cache for 1 hour
                self::$cache->save($cacheItem);
            }
            return $tableInfo;
        } catch (\PDOException $e) {
            echo ("PDOException in getTableInfo: " . $e->getMessage());
            return null;
        }
    }

    //should return data from product table 
    public static function related(string $foreignKey): array
    {


        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1];

        // $Key = $foreignKey; //output : product_id

        $childTable = $caller['function']; //output: favorite

        $userid = users('user_id'); //output: 1

        $parentTable = Helpers::getTableName(static::class); //output: product

        $table = new Generate();

        // Set the parent table
        $table->setParentTable($childTable);

        // Define the SELECT query
        // dd($childTable);

        $query = $table->select('p.* ,c.*')
            ->join("$parentTable c", "p.$foreignKey = c.$foreignKey")
            ->where('p.user_id = :user_id')
            ->getSql();
        // vd($query);
        self::$db = Application::db();
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(":user_id", $userid);
        // dd($stmt);
        $data = $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return $data;
        }
        return [];
    }
    public static function ajax(array $ajax)
    {




        $key = $ajax['method'] . '::' . $ajax['url'];
        // vd($key);
        if (isset(self::$ajax[$key])) {
            return self::$ajax[$key];
        } else {
            self::$ajax[$key] = $ajax;
            return false;
        }
    }
}
