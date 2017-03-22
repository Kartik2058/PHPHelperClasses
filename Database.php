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
    
    public static function create($data) {
        $class = new static;
        $query = "INSERT INTO {$class->table_name} (";
        $left_column_names = [];
        foreach($class->column_names as $column_name) {
            if(isset($data[$column_name])) {
                $left_column_names[] = $column_name;
            }
        }
        $column_names = implode(',', $left_column_names);
        $query .= $column_names . ") VALUES (";
        $i = 0;
        $len = count($left_column_names);
        $params = [];
        foreach ($left_column_names as $column_name) {
            $class->$column_name = $data[$column_name];
            $params[] = $data[$column_name];
            if ($i == $len - 1) {
                $query .= "?";
            } else {
                $query .= "?,";
            }
            $i++;
        }
        $query .= ")";
        $result = $class->query($query, 1);
        $result->execute($params);
        $class->id = $class->connection->lastInsertId();
        return $class;
    }
    
    public function update($data) {
        $where_column_name = $this->getPrimaryKeyName();
        $value = $this->$where_column_name;
        $left_column_names = [];
        foreach($this->column_names as $column_name) {
            if(isset($data[$column_name])) {
                $left_column_names[] = $column_name;
            }
        }
        $query = "UPDATE {$this->table_name} SET ";
        $i = 0;
        $len = count($left_column_names);
        $params = [];
        foreach ($left_column_names as $column_name) {
            if(isset($data[$column_name])) {
                $params[] = $data[$column_name];
                $this->$column_name = $data[$column_name];
                if ($i == $len - 1) {
                    $query .= "$column_name = ?";
                } else {
                    $query .= "$column_name = ?,";
                }
            }
            $i++;
        }
        $query .= " WHERE $where_column_name = ?";
        $params[] =  $value;
        $result = $this->query($query, 1);
        $result->execute($params);
    }
    
    public function delete($column_name=null,$value=null) {
        if($column_name == null && $value == null) {
            $column_name = $this->getPrimaryKeyName();
            $value = $this->$column_name;
        }
        $query = "DELETE FROM {$this->table_name} WHERE $column_name = ?";
        $result = $this->query($query,1);
        $result->execute([$value]);
        return $value;
    }
    
}
