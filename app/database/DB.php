<?php
namespace App\DB;

use App\DB\QueryBuilder;

class DB {

    /**
     * Database credentials.
     */
    private static ?string $dbtype = null;
    private static ?string $dbhost = null;
    private static ?string $dbport = null;
    private static ?string $dbname = null;
    private static ?string $dbuser = null;
    private static ?string $dbpass = null;

    /**
     * PDO options.
     */
    private static array $options = [
        \PDO::ATTR_EMULATE_PREPARES      => false,
        \PDO::ATTR_ERRMODE               => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE    => \PDO::FETCH_ASSOC,
    ];

    /**
     * Database connection.
     */
    private static ?\PDO $connection = null;

    /**
     * Database tables whitelist.
     */
    private static ?array $tables = null;

    /**
     * Get the same instance of this class.
     */
    private static function getInstance(): QueryBuilder {
        // Set credentials.
        static::$dbtype = $_ENV['DB_TYPE'];
        static::$dbhost = $_ENV['DB_HOST'];
        static::$dbport = $_ENV['DB_PORT'];
        static::$dbname = $_ENV['DB_NAME'];
        static::$dbuser = $_ENV['DB_USER'];
        static::$dbpass = $_ENV['DB_PASS'];

        // Connect to database.
        if ( is_null(static::$connection) ) {
            static::connect();
        }

        // Load database tables as additional validation layer.
        if ( 'yes' === strtolower($_ENV['DB_CHECK_TABLES']) && is_null(static::$tables) ) {
            static::loadTables();
        }

        // Return the query builder.
        return new QueryBuilder( static::$connection, static::$tables );
    }

    /**
     * Connect to database.
     */
    private static function connect(): void {
        try {
            static::$connection = new \PDO(
                static::$dbtype.':host='.static::$dbhost.':'.static::$dbport.';dbname='.static::$dbname, 
                static::$dbuser, 
                static::$dbpass, 
                static::$options
            ); 
        } catch (\PDOException $e) {
            throw new \Exception('Connection error: ' . $e->getMessage());
        }
    }

    /**
     * Get all the tables of current database.
     */
    private static function loadTables(): void {
        $tables = (array) [];
        $conn = static::$connection;

        foreach ($conn->query('SHOW TABLES') as $row) {
            $tables = [...$tables, ...array_values($row)];
        }

        static::$tables = $tables;
    }

    /**
     * Call a static method from this class referred to QueryBuilder instance.
     */
    public static function __callStatic($method, $arguments): QueryBuilder {
        $instance = static::getInstance();

        /**
         * Statically call one of the methods inside the query builder class.
         */
        return call_user_func_array(
            [$instance, $method],
            $arguments
        );
    }

}