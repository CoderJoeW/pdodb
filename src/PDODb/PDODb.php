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
    public function __construct($username = '', $password = '', $host = '', $dbName = '', $connectionType = 'mysql', array $attributes){
        try{
            $this->connection = new PDO("$connectionType:host=$host;port=3306;dbname=$dbName;charset=utf8mb4",$username,$password);

            foreach($attributes as $k => $v){
                $this->connection->setAttribute($k, $v);
            }
        }catch(PDOException $e){
            error_log($e->getMessage());
            throw $e;
        }
    }

   /**
   * @param string $query
   * @param array<mixed> $params
   * @return string|null
   */
    public function insert(string $query, array $params = []): ?string {
        $statement = $this->executeStatement($query, $params);

        if ($statement) {
            return $this->connection->lastInsertId();
        }

        return null;
    }

    /**
    * @param string $query
    * @param array<mixed> $params
    * @return array<mixed>
    */
    public function select($query, $params = []): array {
        $statement = $this->executeStatement($query, $params);

        if (!$statement) {
            // Should probably throw an exception
            return [];
        }

        return $statement->fetchAll();
    }

    /**
    * Execute an update SQL query.
    *
    * @param string $query The SQL query string.
    * @param array<mixed> $params The query parameters.
    * @return bool True on success, false on failure.
    */
    public function update(string $query, array $params = []): bool {
        $statement = $this->executeStatement($query, $params);
        return $statement !== null;
    }


    /**
     * @param string $query
     * @param array<mixed> $params
    */
    public function remove($query, $params = []){
        $this->executeStatement($query,$params);
    }
    
    /**
    * Create a parameter string for use in a prepared SQL statement.
    *
    * @param array<mixed> $data
    * @param array<mixed> $excludedParameters
    * @return string
    */
    public function createParameterString(array $data, array $excludedParameters = []): string {
        $parts = [];

        foreach ($data as $key => $value) {
            // Skip excluded parameters
            if (in_array($key, $excludedParameters)) {
                continue;
            }

            // If key contains a period, it's considered as "table.column"
            if (strpos($key, '.') !== false) {
                list($table, $column) = explode('.', $key);
                $parts[] = "$key=:$column";
            } else {
                $parts[] = "$key=:$key";
            }
        }

        return implode(',', $parts);
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
