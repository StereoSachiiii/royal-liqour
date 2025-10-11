<?php
// db/connection.php
require_once __DIR__ . '/../config/constants.php';
class Database{

    private static ?PDO $pdo = null; //nullable
public static  function getPdo(): PDO {
    if(self::$pdo === null){


        
    $host = DB_HOST;
    $db   = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    $port = DB_PORT;
    $charset = 'utf8mb4';

    $dsn = "pgsql:host={$host};port={$port};dbname={$db};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,                  // use native prepares
    ];

     try {
        self::$pdo = new PDO($dsn, $user, $pass, $options);
        return self::$pdo;

        } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw new RuntimeException('Database connection failed.');
    }
    }
    return self::$pdo;

   
}
}