<?php
// db/connection.php
class Database{
private static ?PDO $pdo = null; //nullable
public static  function getPdo(): PDO {
    if(self::$pdo === null){
     
    // Load config directly
    $config = require __DIR__ . '/../config/config.php';
    $dbConfig = $config['database'];
    
    $host = $dbConfig['host'];
    $db   = $dbConfig['name'];
    $user = $dbConfig['user'];
    $pass = $dbConfig['pass'];
    $port = $dbConfig['port'];
    

    // Use PostgreSQL (pgsql) DSN
    $dsn = "pgsql:host={$host};port={$port};dbname={$db};";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,                  // use native prepares
    ];

     try {
        self::$pdo = new PDO($dsn, $user, $pass, $options);
        return self::$pdo;

        } catch (PDOException $e) {
        die(" PDO Connection Error: " . $e->getMessage());
    }    
}
    return self::$pdo;

   
    }
} ?>