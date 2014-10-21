<?php
namespace MyApp;


class Config
{
    static $confArray;

    public static function read($name)
    {
        return self::$confArray[$name];
    }

    public static function write($name, $value)
    {
        self::$confArray[$name] = $value;
    }

}
 
$_dbfile = 'you sqlite db file';
//api
Config::write('api.host', 'https://you server');
// db
Config::write('db.file', sprintf($_dbfile));
Config::write('db.dsn',  sprintf('sqlite:%s', $_dbfile));
Config::write('db.port', '');
Config::write('db.basename', '');
Config::write('db.user', 'root');
Config::write('db.password', '');
//wss
Config::write('wss.username', 'username');
Config::write('wss.password', 'password sssssssssssssssss');
