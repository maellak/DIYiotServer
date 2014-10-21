<?php
namespace MyApp;

class Core
{
    public $dbh; // handle of the db connexion
    private static $instance;

    private function __construct()
    {
        $this->dbh = new \PDO(sprintf('sqlite:%s', $dbfile));
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    // others global functions
}
