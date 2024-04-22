<?php

namespace App;

use Dotenv\Dotenv;

final class Init {

    /**
     * Class Instance.
     */
    private static ?Init $instance = null;

    /**
     * Project base DIRectory path.
     */
    public string $basedir = '';

    /**
     * Instance this class.
     */
    public static function getInstance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->set_basedir();
        $this->require_autoload();
        $this->set_env();
        $this->require_includes();
    }

    /**
     * Set base url.
     */
    private function set_basedir() {
        $path = __DIR__;
        $pathArr = explode('\\', $path);

        if ( 'app' === end( $pathArr ) ) {
            $this->basedir = dirname( $path ) . '\\';
        } else {
            $this->basedir = $path . '\\';
        }
    }

    /**
     * Require the autoload.
     */
    private function require_autoload() {
        require_once $this->basedir . 'vendor/autoload.php';
    }

    /**
     * Set environmental variables.
     */
    private function set_env() {
        Dotenv::createImmutable( $this->basedir )->load();
    }

    /**
     * Require includes.
     */
    private function require_includes() {                
        require_once $this->basedir . 'app/database/enums/DBEnum.php';
        require_once $this->basedir . 'app/database/QueryBuilder.php';
        require_once $this->basedir . 'app/database/DB.php';
    }

}