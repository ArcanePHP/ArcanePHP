<?php

namespace Database;


use Core\Application;
use Core\Config;
use Core\DB;
use Migration\AdminTable;
use Migration\CartTable;
use Migration\CategoryTable;
use Migration\ProductTable;
use Migration\UserTable;

class Migratables
{

    protected static DB $db;
    public function __construct()
    {
        $app = new Application(new Config($_ENV));
        $this::$db = $app::$db;
        $this->up($this::$db);
    }

    public function up($db)
    {
        // $table = new ProductTable($db);

        $ad = new AdminTable($db);
        $pt = new ProductTable($db);
        $user = new UserTable($db);
        $table = new CategoryTable($db);

        $card = new CartTable($db);
    }
}
;
