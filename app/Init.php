<?php

namespace App;

use Dotenv\Dotenv;

final class Init {

    public string $baseurl = '';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->set_baseurl();
        $this->require_autoload();
        $this->set_env();
        $this->require_includes();
    }

    /**
     * Set base url.
     */
    private function set_baseurl() {
        $path = __DIR__;
        $pathArr = explode('\\', $path);

        if ( 'app' === end( $pathArr ) ) {
            $this->baseurl = dirname( $path ) . '\\';
        } else {
            $this->baseurl = $path . '\\';
        }
    }

    /**
     * Require the autoload.
     */
    private function require_autoload() {
        require_once $this->baseurl . 'vendor/autoload.php';
    }

    /**
     * Set environmental variables.
     */
    private function set_env() {
        Dotenv::createImmutable( $this->baseurl )->load();
    }

    /**
     * Require includes.
     */
    private function require_includes() {
        require_once $this->baseurl . 'app/utils/Rand.php';
                
        require_once $this->baseurl . 'app/database/enums/DBEnum.php';
        require_once $this->baseurl . 'app/database/QueryBuilder.php';
        require_once $this->baseurl . 'app/database/DB.php';
    }

}