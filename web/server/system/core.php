<?php


//dbfile
$_dbfile = '../../db/your_dbfile';


//ssh
$_sshhost="ssh server";
$_sshport="port for ssh server";

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

//ssh
diyConfig::write('ssh.host', $_sshhost);
diyConfig::write('ssh.port', $_sshport);

// db
diyConfig::write('db.file', sprintf($_dbfile));
diyConfig::write('db.dsn',  sprintf('sqlite:%s', $_dbfile));
diyConfig::write('db.port', '');
diyConfig::write('db.basename', '');
diyConfig::write('db.username', 'root');
diyConfig::write('db.password', '');
//ssh
diyConfig::write('ssh.home', $sshhome);


// e-mail server settings
diyConfig::write('mail.fromuser', $fromuser);
diyConfig::write('mail.smtpserver',  $smtpserver);
diyConfig::write('mail.smtpport',  $smtpport);
