<?php


//dbfile
$_dbfile = '../../db/oauth.sqlite';

//api
$_apihost="https://your server";

//ssh
$sshhome="path to ssh dir witch contain the buplic keys"; 

// ***GIT*** 
// ***GitGit*** 

class diyConfig
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
 
//debug
diyConfig::write('debug', 1); // 1 = on 0 = off

//api
diyConfig::write('api.host', $_apihost);

// db
diyConfig::write('db.file', sprintf($_dbfile));
diyConfig::write('db.dsn',  sprintf('sqlite:%s', $_dbfile));
diyConfig::write('db.port', '');
diyConfig::write('db.basename', '');
diyConfig::write('db.username', 'root');
diyConfig::write('db.password', '');
//ssh
diyConfig::write('ssh.home', $sshhome);
