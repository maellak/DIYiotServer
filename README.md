# DIYiotTools

Το έργο DIYiotTools παρέχει Rest API και εργαλεία για τη δημιουργία Network-enabled Arduino projects


## To DIYiotTools παρέχει


Network-enabled Arduino
	Wireless ή  ethernet, έχει επίσης λειτουργικά την δυνατότητα σύνδεσης σε δίκτυο 3G/GPRS
Over-the-air/on-the-fly programming
Online monitoring & real-time data streaming
Rest Api για δημιουργία διεπαφής για Web/Phone

## How to Use It

### Download
You can use the DIYiotTools code AS-IS!  No need to build or recompile--just clone this repo and use the files in the `web` folder.  

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

4. Edit  client/myhost.php

 4.1 run "php client/client-gettoken.php"

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

5.  see examples in the directory "client"

Happy Coding :-)


#  Required dependencies

Make sure you have all dependencies
For more information see web/swagger-ui/README.md and  swagger/swagger-php/readme.md

# License
	See LICENSE
