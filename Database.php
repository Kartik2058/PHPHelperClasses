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
    
    public function query($sql, $options=null) {
        if ($options == null) {
            return $this->connection->query($sql);
        } else if ($options == 1) {
            return $this->connection->prepare($sql);
        }
    }
    
    public static function find_all() {
        $class = new static;
        $query = $class->query("SELECT * FROM {$class->table_name}");
        $query->setFetchMode(PDO::FETCH_CLASS,static::class);
        $data = $query->fetchAll();
        return $data;
    }
    
    public static function find_by_id($id, $id_field=null) {
        $class = new static;
        if ($id_field == null) {
            $id_field = $class->getPrimaryKeyName();
        }
        $query = $class->query("SELECT * FROM {$class->table_name} WHERE $id_field = $id LIMIT 1");
        $query->setFetchMode(PDO::FETCH_CLASS,static::class);
        $data = $query->fetch();
        return $data;
    }
    
    public static function find_by_where($where_statement=null, $where_params=null, $columns=null, $order_type=null, $order_column=null, $limit=null) {
        $class = new static;
        $sql = "";
        if ($columns == null) {
            $sql .= "SELECT * FROM {$class->table_name}";
        } else {
            $newColumns = [];
            foreach ($columns as $column) {
                if (in_array($column, $class->column_names)) {
                    $newColumns[] = $column;
                }
            }
            $sql .= "SELECT ".implode($newColumns,',')." FROM {$class->table_name}";
        }
        
        if ($where_statement != null) {
            $sql .= " WHERE " . $where_statement;
        }
        
        if ($order_type != null && $order_column != null) {
            $sql .= " ORDER BY " . $order_column . " " . $order_type;
        }
        
        if ($limit != null) {
            $sql .= " LIMIT " . $limit;
        }
        
        $query = $class->query($sql, 1);
        $query->execute($where_params);
        return $query->fetchAll();
    }
    
}