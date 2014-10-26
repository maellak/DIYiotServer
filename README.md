# DIYiotServer

Το DIYiotServer παρέχει Rest API και εργαλεία για τη δημιουργία Network-enabled Arduino projects


## To DIYiotServer παρέχει


Network-enabled Arduino
	Wireless ή  ethernet, έχει επίσης λειτουργικά την δυνατότητα σύνδεσης σε δίκτυο 3G/GPRS
Over-the-air/on-the-fly programming
Online monitoring & real-time data streaming
Rest Api για δημιουργία διεπαφής για Web/Phone

## How to Use It

### Download
You can use the DIYiotServer code AS-IS!  No need to build or recompile--just clone this repo and use the files in the `web` folder.  

Tree Example
```
├── client
│   ├── client-getdevices.php
│   └── client-gettoken.php
├── db
│   └── oauth.sqlite
├── LICENSE-agpl-3.0.txt
├── README.md
├── src
│   └── OAuth2
│       ├── Autoloader.php
│       │   └── UserCredentialsInterface.php
├── ssh
│   ├── Your.pem
│   ├── Your_pubkey.pem
│   ├── Your_privkey.pem
│   ├── privkey.pem
│   ├── pubkey.pem
│   └── rsa <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< READ this
├── swagger
├── tools
│   └── rebuild_db.php
├── tree
├── vendor
│   ├── autoload.php
│   └── composer
│       ├── autoload_classmap.php
│       ├── autoload_namespaces.php
│       ├── autoload_psr4.php
│       ├── autoload_real.php
│       └── ClassLoader.php
└── web   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DocumentRoot <<<<<<<<<<<<<<<<<<<<<
    ├── api
    │   ├── delete
    │   ├── get
    │   │   └── diy_getdevices.php
    │   ├── index.php
    │   ├── post
    │   └── put
    ├── docs
    │   ├── api-docs.json
    │   ├── diyiot.json
    │   ├── index.php
    │   └── v1-tool_data-tool.json
    ├── server
    │   ├── libs
    │   │   └── Slim
    │   └── system
    │       ├── config.php
    │       ├── includes.php
    │       └── methodtypes.php
    └── swagger-ui
```
### Config

#### https 

Follow these steps:

1. install apache, php with PDO, sqlite3

 1.1 creating a virtual host 

	DocumentRoot "path to web dir"
	ServerName [Your Server Name]

	<Directory "path to web dir">
		Options -Indexes
		AllowOverride All
		Require all granted
	</Directory>

 1.2 restart httpd

2. Change into the ssh directory and run "bash ./rsa"

3. Change into the tools directory 

 3.1 Edit ./rebuild_db.php

	Find and Replace '../ssh/pubkey.pem' and '../ssh/privkey.pem', with your file names (see step 2)

	$publicKey  = file_get_contents('../ssh/pubkey.pem');
	$privateKey = file_get_contents('../ssh/privkey.pem');

 3.2 run "php ./rebuild_db.php"

	Check Generated Tables
	- cd db; sqlite3 oauth.sqlite 
	- .tables  (the generated tables)
	- .quit    (exit)
4. edit web/server/system/core.php

	$_dbfile = 'your db file'; 		(created in step 3.2 above)
	$_apihost="your url";			(created in step 1.1 above)
	$sshhome="dir for ssh";			(dir contains the devices keys)
						more info how to do this
						http://stackoverflow.com/questions/8021/allow-user-to-set-up-an-ssh-tunnel-but-nothing-else
						http://www.gnu.org/software/bash/manual/html_node/The-Restricted-Shell.html
						https://wiki.archlinux.org/index.php/Secure_Shell

5. Edit  client/myhost.php

 5.1 run "php client/client-gettoken.php"

	If you see something like this, then your application is ready
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

6.  see examples in the directory "client"

#### wss

1. edit ws/src/MyApp/Config.php file

	$_dbfile = 'your db file'; 	(created in step 3.2 above)
	$_apihost="your url"; 		(created in step 1.1 above)	
	$_wssusername='wssusername'; 	(created in step 3.1 above)
	$_wsspassword='wsspassword';	(created in step 3.1 above)

2. edit /etc/hosts

	change the 127.0.1.1 line to your new wss/api service
	127.0.0.1 <old names>  verifytoken 

	p.x.

	127.0.0.1   localhost localhost.localdomain localhost4 localhost4.localdomain4 verifytoken

	or 

	
	if the https Api is  installed in another host
	then put hier the  ip of the host
	
	p.x.

	192.168.0.10   verifytoken or 195.175.111.10 verifytoken


2. run it

	php ws/ws.php

Happy Coding :-)


#  Required dependencies

Make sure you have all dependencies

For more information see web/swagger-ui/README.md and  swagger/swagger-php/readme.md

# License
	See LICENSE
