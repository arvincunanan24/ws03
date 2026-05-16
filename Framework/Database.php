<?php

namespace Framework;

use PDO;

class Database
{
    public $conn;

    public function __construct($config)
    {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ];

        try {
            $this->conn = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOEXCEPTION $e) {
            throw new Exception('Database failed connection: ' . $e->getMessage());
        }
    }

    public function query($query, $params = [])
    {
        try {
            $sth = $this->conn->prepare($query);

            // Bind named params
            foreach ($params as $param => $value) {
                $sth->bindValue(':' . $param, $value);
            }

            $sth->execute();
            return $sth;
        } catch (PDOException $e) {
            throw new Exception("Query failed{$e->getMessage()}");
        }
    }
}
