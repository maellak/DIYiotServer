<?php

//dbfile
$_dbfile = '../../db/oauth.sqlite';

//ssh server
$_sshhost="ssh_server";
$_sshport="port_for_ssh_server";

//api
$_apihost="https://your_server";

//ssh
$sshhome="../../ssh";
$sshhomekeys="file for authorized_keys";

//e-mail server settings
$fromuser="user@server.gr";
$smtpserver="smtp.server.gr";
$smtpport="25";

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
diyConfig::write('ssh.keys', $sshhomekeys);

// e-mail server settings
diyConfig::write('mail.fromuser', $fromuser);
diyConfig::write('mail.smtpserver',  $smtpserver);
diyConfig::write('mail.smtpport',  $smtpport);
