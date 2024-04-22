<?php
namespace App\DB;

use App\DB\QueryBuilder;

class DB {

    /**
     * Main instance of this class.
     */
    private static ?QueryBuilder $instance = null;

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
    private static array $tables = [];

    /**
     * Get the same instance of this class.
     */
    private static function getInstance() {
        if ( is_null(self::$instance) ) {
            // Set credentials.
            self::$dbtype = $_ENV['DB_TYPE'];
            self::$dbhost = $_ENV['DB_HOST'];
            self::$dbport = $_ENV['DB_PORT'];
            self::$dbname = $_ENV['DB_NAME'];
            self::$dbuser = $_ENV['DB_USER'];
            self::$dbpass = $_ENV['DB_PASS'];

            // Connect to database.
            self::connect();

            // Load database tables as additional validation layer.
            self::loadTables();

            // Set a new instance of QueryBuilder setting database connection.
            self::$instance = new QueryBuilder( self::$connection, self::$tables );
        }

        return self::$instance;
    }

    /**
     * Connect to database.
     */
    private static function connect(): \PDO {
        try {
            self::$connection = new \PDO(
                self::$dbtype.':host='.self::$dbhost.':'.self::$dbport.';dbname='.self::$dbname, 
                self::$dbuser, 
                self::$dbpass, 
                self::$options
            ); 
        } catch (\PDOException $e) {
            echo 'Connection error: ' . $e->getMessage();
        }

        return self::$connection;
    }

    /**
     * Get all the tables of current database.
     */
    private static function loadTables() {
        $tables = (array) [];
        $conn = self::$connection;

        foreach ($conn->query('SHOW TABLES') as $row) {
            $tables = [
                ...$tables,
                ...array_values($row)
            ];
        }

        self::$tables = $tables;
    }

    /**
     * Call a static method from this class referred to QueryBuilder instance.
     */
    public static function __callStatic($method, $arguments): QueryBuilder {
        $instance = self::getInstance();

        /**
         * Statically call one of the methods inside the query builder class.
         */
        return call_user_func_array(
            [$instance, $method],
            $arguments
        );
    }

}