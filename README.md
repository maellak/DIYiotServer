# DIYiotServer

## Σκοπός (Greek, English follows)

Το DIYiotServer παρέχει REST API και εργαλεία για τη δημιουργία network-enabled Arduino projects.

To DIYiotServer παρέχει:

* Network-enabled Arduino, wireless ή ethernet, έχει επίσης λειτουργικά την δυνατότητα σύνδεσης σε δίκτυο 3G/GPRS
* Over-the-air/on-the-fly programming
* Online monitoring & real-time data streaming
* REST API για δημιουργία διεπαφής για Web/κινητά

## Introduction

DIYiotServer provides a REST API and tools for creating network-enabled Arduino projects.

DIYiotServer provides:


* Network-enabled Arduino, wireless or over ethernet, can also connect to 3G/GPRS networks
* Over-the-air/on-the-fly programming
* Online monitoring & real-time data streaming
* REST API for creating UIs for the Web or smartphones

## How to Use It

### Required dependencies

* Apache - The Apache HTTP Server ("httpd") (http://httpd.apache.org)  
	  - you can use lighttpd or nginx but is not tested yet

* PHP with PDO (http://php.net)  
	- PHP is a popular general-purpose scripting language that is especially suited to web development.  
	- PDO The PHP Data Objects extension defines a lightweight, consistent interface for accessing databases in PHP.  
	    PDO provides a data-access abstraction layer, which means that, regardless of which database you're using, you use the same functions to issue queries and fetch data.  

* sqlite3 (http://www.sqlite.org)  
	- SQLite is a software library that implements a self-contained, serverless, zero-configuration, transactional SQL database engine.  
	 *Alternative*  
	- In fact that this project use PDO you can also use any of this databases systems  
	  See http://php.net/manual/en/pdo.drivers.php

* Ratchet - WebSockets for PHP (http://socketo.me)

* React - Event-driven, non-blocking I/O with PHP (http://reactphp.org/)

* ZeroMQ - Carries messages across inproc, IPC, TCP, TPIC, multicast (http://zeromq.org)

* HAProxy - The Reliable, High Performance TCP/HTTP Load Balancer (http://www.haproxy.org)

* Autobahn|JS - provides an open-source implementation of The Web Application Messaging Protocol (WAMP) (http://autobahn.ws/js)
	- Autobahn is used in https://github.com/diyiot-tools/DIYiotWebClient.   
	See https://github.com/diyiot-tools/DIYiotWebClient/blob/master/example-websocket.html  

**Optional**

* Supervisor - A Process Control System (http://supervisord.org)

* API Documentation  
See [swagger-ui](web/swagger-ui/README.md) and [swagger-php](swagger/swagger-php/readme.md).

### Download

You can use the DIYiotServer code as is, no need to build or recompile.
Just clone this repo and use the files in the `web` folder.

### Configuration steps

#### Set up HTTPS in Apache

Follow these steps to set up the website in the Apache web server:

Edit the apache configuration file (e.g. `/etc/httpd/conf/httpd.conf` in CentOS):

```
DocumentRoot /var/www/html/web
ServerName example.com

<Directory /var/www/html/web>
    Options -Indexes
    AllowOverride All
    Require all granted
</Directory>
```

Generate SSL keys:

```
cd ssh/
./create_rsa_key.sh
```

In `tools/insert-testdata_db.php`, replace all occurrences of `../ssh/pubkey.pem`
and `../ssh/privkey.pem`, with your file names:

```
$publicKey  = file_get_contents('../ssh/pubkey.pem');
$privateKey = file_get_contents('../ssh/privkey.pem');
```

Insert test data and and check the generated tables:

```
cd tools/
php ./rebuild_db.php
php ./insert-testdata_db.php
cd ../db
sqlite3 oauth.sqlite
.tables
.quit
```

Edit `web/server/system/core.php`:

```
$_dbfile = 'path_to_db_file'; # (by default oauth.sqlite)
$_apihost="https://your_url"; # (your FQDN)
$sshhome="path_to_ssh_dir";   # (dir that contains the devices keys)
```

More info how to do this:
* http://stackoverflow.com/questions/8021/allow-user-to-set-up-an-ssh-tunnel-but-nothing-else
* http://www.gnu.org/software/bash/manual/html_node/The-Restricted-Shell.html
* https://wiki.archlinux.org/index.php/Secure_Shell

Edit `client/myhost.php`:

```
$host="url";            # (your FQDN)
$username="username";   # (set a username)
$password="password";   # (set a password)
```

Run `php client/client-gettoken.php`.

If you see something like this, then your application is ready:

```
array(4) {
  ["access_token"]=>
  string() "token"
  ["expires_in"]=>
  int(3600)
  ["token_type"]=>
  string(6) "bearer"
  ["scope"]=>
  string(15) "test_admin main"
}
```

See the examples in the `client` directory.

#### wss

Edit `ws/src/MyApp/Config.php`:

```
$_dbfile = 'path_to_db_file';   # (by default oauth.sqlite)
$_apihost="https://your_url";   # (your FQDN)
$_wssusername='wssusername';    # (same as client/myhost.php)
$_wsspassword='wsspassword';    # (same as client/myhost.php)
```

Change the 127.0.1.1 line to your new wss/API service,
by editing `/etc/hosts` and appending `verifytoken` to the localhost entry.
For example:

    127.0.0.1   localhost localhost.localdomain localhost4 localhost4.localdomain4 verifytoken

If the https API is installed on another host, then put here the IP of the host,
for example:

    192.168.0.10   verifytoken

Run it with:

````
php ws/ws.php
````

Happy Coding :-)

# License

AGPLv3, see `LICENSE`.
