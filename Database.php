<?php

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "test");

require_once("Inflect.php");

class Database {
    
    public function __construct() {
        $this->connection = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASS);
        $this->table_name = Inflect::pluralize(strtolower(static::class));
        $this->column_names = $this->getColumnNames();
    }
    
    public function getColumnNames(){
        $result = $this->query("SHOW COLUMNS FROM {$this->table_name}");
        if ($result->rowCount() > 0) {
            $column_names = [];
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
              $column_names[] = $row['Field'];
            }
            return $column_names;
        }
    }
    
    public function getPrimaryKeyName() {
        $result = $this->query("SHOW KEYS FROM {$this->table_name} WHERE Key_name = 'PRIMARY'");
        if ($result->rowCount() == 1) {
            return $result->fetch(PDO::FETCH_ASSOC)["Column_name"];
        } else {
            return null;
        }
    }
    
}