<?php
namespace MyApp;



//dbfile
$_dbfile = 'path to db file';

//api
$_apihost="https://your server";

//wss
$_wssusername='username';
$_wsspassword='password';

// ***GIT*** 
// ***GitGit*** 


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
 
//api
Config::write('api.host', $_apihost);

// db
Config::write('db.file', sprintf($_dbfile));
Config::write('db.dsn',  sprintf('sqlite:%s', $_dbfile));
Config::write('db.port', '');
Config::write('db.basename', '');
Config::write('db.user', 'root');
Config::write('db.password', '');

//wss
Config::write('wss.username', $_wssusername);
Config::write('wss.password', $_wsspassword);

