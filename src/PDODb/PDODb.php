<?php

namespace PDODb;

use PDO;
use PDOException;

class Database{
    private PDO $connection;

    /**
     * @param string $username
     * @param string $password
     * @param string $host
     * @param string $dbName
     * @param string $connectionType
    */
    public function __construct($username = '', $password = '', $host = '', $dbName = '', $connectionType = 'mysql',array $attributes){
        try{
            $this->connection = new PDO("$connectionType:host=$host;port=3306;dbname=$dbName;charset=utf8mb4",$username,$password);

            foreach($attributes as $k => $v){
                $this->connection->setAttribute($k, $v);
            }
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    /**
     * @param string $query
     * @param array<mixed> $params
    */
    public function insert($query ,$params = []): string|bool{
        $this->executeStatement($query,$params);
        return $this->connection->lastInsertId();
    }

    /**
     * @param string $query
     * @param array<mixed> $params
    */
    public function select($query, $params = []){
        $statement = $this->executeStatement($query,$params);
        return $statement->fetchAll();
    }

    /**
     * @param string $query
     * @param array<mixed> $params
    */
    public function update($query, $params = []){
        $this->executeStatement($query,$params);
    }

    /**
     * @param string $query
     * @param array<mixed> $params
    */
    public function remove($query, $params = []){
        $this->executeStatement($query,$params);
    }
    
    /**
     * @param array<mixed> $data
     * @param array<mixed> $excludedParameters
    */
    public function createParameterString($data,$excludedParameters = []){
        $parameterString = "";
        
        foreach($data as $key => $value){
            if(!in_array($key,$excludedParameters)){
                if(strpos($key,'.') !== false){
                    $parts = explode('.',$key);

                    $table = $parts[0];
                    $column = $parts[1];

                    $parameterString .= "$key=:$column,";
                }else{
                    $parameterString .= "$key=:$key,";
                }
            }
        }
        
        return substr($parameterString,0,-1);
    }

    /**
     * @param string $query
     * @param array<mixed> $params
    */
    private function executeStatement($query,$params = []){
        $statement = $this->connection->prepare($query);

        try{
            $statement->execute($params);
        } catch (PDOException $e){
            error_log($e);
            error_log(print_r($params));
            error_log($query);
            return;
        }
        
        return $statement;
    }
}
?>
