<?php

$dbfile = '../db/oauth.sqlite';

if (file_exists($dbfile)) {
    unlink($dbfile);
}

if (!is_writable(__DIR__)) {
    if (!@chmod(__DIR__, 0777)) {
        throw new Exception("Unable to write to $dbfile");
    }
}

// rebuild the DB
$db = new PDO(sprintf('sqlite:%s', $dbfile));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
/*
	CREATE TABLE oauth_clients (
		client_id 	VARCHAR(80) 	NOT NULL	COMMENT 'Unique client identifier',
		client_secret 	VARCHAR(80) 	NOT NULL 	COMMENT 'Client secret',
		redirect_uri 	VARCHAR(2000) 			COMMENT 'Redirect URI used for Authorization Grant',
		grant_types 	VARCHAR(80) 			COMMENT 'Space-delimited list of grant types permitted, null = all',
		scope 		VARCHAR(4000) 			COMMENT 'Space-delimited list of approved scopes',
		user_id 	INT UNSIGNED 			COMMENT 'FK to oauth_users.user_id',
		sshhost		VARCHAR(2000) 			COMMENT 'ssh host for the client',
		sshport		VARCHAR(2000) 			COMMENT 'port for ssh host for the client',
		apihost		VARCHAR(2000)			COMMENT 'api host for the client',
		apiport		INT UNSIGNED			COMMENT 'apiport for on the fly commands',
		dataport	INT UNSIGNED			COMMENT 'data from dev',
		public_key 	VARCHAR(2000) 			COMMENT 'Public key for encryption',
		PRIMARY KEY (client_id)
	);
 */
// ******************************************** info ************************************
// o kyrioteros pinakas
// vlepe ta scholia parapano
// *************************** more info *******************
// otan o clien. dev kai user, ginete invite se kapio scope
// prepei afto na mpei sto pedio  scope
//
// otan o client einai dev
// tote chriasomaste ta ipoloipa
//              sshhost        
//              sshport        
//              apihost        
//              apiport        
//              dataport        
//              public_key      
// diafoerita NULL

// ******************************************** info ************************************
$oauth_clients = <<<EOD
	CREATE TABLE oauth_clients (
		client_id 	VARCHAR(80) 	NOT NULL,
		client_secret 	VARCHAR(80) 	NOT NULL,
		redirect_uri 	VARCHAR(2000),	
		grant_types 	VARCHAR(80),
		scope 		VARCHAR(4000),
		user_id 	INT UNSIGNED,
		sshhost		VARCHAR(2000),
		sshport		INT UNSIGNED,
		apihost		VARCHAR(2000),
		apiport		INT UNSIGNED,
		dataport	INT UNSIGNED,
		tty 		VARCHAR(80),
		baud 		VARCHAR(80),
		public_key 	VARCHAR(2000),
		PRIMARY KEY (client_id)
	);
EOD;
$db->exec($oauth_clients);


// ******************************************** info ************************************
// plirofories gia tous users
// epissis einai chrisimos aftos o pinakas gia to register
// ******************************************** info ************************************
$oauth_users = <<<EOD
	CREATE TABLE oauth_users (
		user_id 	INT UNSIGNED NOT NULL,
		first_name 	VARCHAR(80),
		last_name 	VARCHAR(80),
		email 		VARCHAR(2000),
		email_verified 	BOOLEAN,
        email_ver_code	VARCHAR(2000),
		PRIMARY KEY (user_id)
	);
EOD;
$db->exec($oauth_users);

// ******************************************** info ************************************
// pinakas gia ta scopes
// mono ta scopes pou iparchoun edo mporoun kai na chrissimopiithoun
// ara 
// prota prepei na valoume kapio scope edo
// kai meta mporoume na to chrissimopiisoume se kapio device
// ******************************************** info ************************************
$oauth_scopes = <<<EOD
 	CREATE TABLE oauth_scopes (
	 	scope TEXT NOT NULL,
	 	is_default BOOLEAN,
	 	PRIMARY KEY (scope)
 	);
EOD;
$db->exec($oauth_scopes);

// ******************************************** info ************************************
// pinakas gia ta keys se sxessi me tous client
// to key edo gia ta devs tha einai diaforetiko gia kathe dev   
// o client user tha echei to geniko  
// *************** more info **************
// to key tou dev tha ginete download apo ton client user giati tha priepei na mpei sto dev gia auth 
// giafto kai einai diaforetiko
// ean itan idio gia olous
// devclient kai clientuser tote 
// efosson dinoume to key gia na mpei sto den
// einai diathessimo kai ston user
// opote mporei na spasei olo to systima para poli apla
// diladi tha klidoname
// alla tha petousame to klidi apo to parathiro
// opote opios thelei tha mporei na mpei
// messa :-)
// ******************************************** task 21 ************************************
// kata tin eisagogi tou dev
// prepei na ginete kai ena neo kleidi
// me tis schetikes egrafes
// ******************************************** info ************************************
$oauth_public_keys = <<<EOD
 	CREATE TABLE oauth_public_keys (
	 	client_id VARCHAR(80),
	 	public_key VARCHAR(2000),
	 	private_key VARCHAR(2000),
	 	encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
 	);
EOD;
$db->exec($oauth_public_keys);

// ******************************************** info ************************************
// pinakas gia plirofories sxetika me ta organisation
// perigrafi
// se pion aniki
// se pio client
// se ena organisation mporoun na anikoun poloi chistes
// ftanei na 
// 1. stili ena invite o owner
// 2. na ginei apodekto apo ton christi
// 3. to melos mporei naechei 3 diaforetika scopes
//		mono view               
//	 	developer, view 
//		ola devel view kai admin
// ******************************************** info ************************************
$oauth_organisations = <<<EOD
 	CREATE TABLE oauth_organisations (
	 	organisation TEXT NOT NULL,
	 	client_id VARCHAR(80),
	 	desc TEXT,
	 	PRIMARY KEY (organisation)
 	);
EOD;
$db->exec($oauth_organisations);

// ******************************************** info ************************************
// pinakas gia plirofories sxetika me ta ports
// pio port
// se pion christi
// gia na vriskoume grigora ta ports pou echoume dossi
// ******************************************** info ************************************
$oauth_ports = <<<EOD
 	CREATE TABLE oauth_ports (
	 	port INT UNSIGNED NOT NULL,
	 	client_id VARCHAR(80),
	 	PRIMARY KEY (port)
 	);
EOD;
$db->exec($oauth_ports);

// ******************************************** info ************************************
// pinakas gia entoles pou mporoun na trexoun messo to dev gateway
// exec = lektiko pou erchete ston poro
// diyiot = i entoli pou tha trexei sto device
// ******************************************** info ************************************
$oauth_ports = <<<EOD
        CREATE TABLE oauth_diyexec (
                exec VARCHAR(80) NOT NULL,
                diyexec VARCHAR(200),
                desc TEXT,
                PRIMARY KEY (exec)
        );
EOD;

// ******************************************** info ************************************
// pinakas gia plirofories sxetika me to device
// perigrafi
// se pion aniki
// se pio arganisation
// ti status echei    private   org  public
// ti mode echei    devel i production
// public_key ginete kata tin egktastassi tou dev
// chrissimoiite gia ssh open ports
// ****************************** more info *****************************************
// to device einai to client_id tou dev
// to client_id einai to client name tou user sto opio aniki
// public_key_active yes/no analoga ean ginei oi scetikes allages gia na doulepsi o user
// ******************************************** info ************************************
$oauth_devices = <<<EOD
 	CREATE TABLE oauth_devices (
	 	device VARCHAR(80) NOT NULL,
	 	device_desc TEXT NOT NULL,
	 	organisation TEXT NOT NULL,
	 	client_id VARCHAR(80) NOT NULL,
		status VARCHAR(10) NOT NULL,
		mode VARCHAR(10) NOT NULL,
	 	private_key VARCHAR(2000),
	 	public_key VARCHAR(2000),
	 	public_key_active VARCHAR(10),
	 	PRIMARY KEY (device)
 	);
EOD;
$db->exec($oauth_devices);

//$oauth_user_devices = <<<EOD
// 	CREATE TABLE oauth_user_devices (
//	 	id INTEGER PRIMARY KEY AUTOINCREMENT,
//	 	device VARCHAR(80) NOT NULL,
//	 	device_desc TEXT NOT NULL,
//	 	organisation TEXT NOT NULL,
//	 	client_id VARCHAR(80) NOT NULL
// 	);
//EOD;
//$db->exec($oauth_user_devices);

// ******************************************** info ************************************
// pinakas gia plirofories sxetika me to session ston wss
// poios client tou https api
// poios wss_user tou wss
// poio session echei session tou wss
// gia na vriskoume tin sischetissi metaxi https kai wss
// ******************************************** info ************************************
$oauth_https_wss = <<<EOD
 	CREATE TABLE oauth_https_wss (
	 	client_id VARCHAR(80) NOT NULL,
	 	wss_user VARCHAR(80) NOT NULL,
	 	session VARCHAR(2000) NOT NULL,
	 	PRIMARY KEY (client_id)
 	);
EOD;
$db->exec($oauth_https_wss);
// ******************************************** info ************************************
// ******************************************** task 20 ************************************
// o pinakas iparchei gia na enimeronete o server sxetika me to archiko setting tou devices
// diladi
// 1. sto devices efosson valoume to openwrt trechoume    make-all
// 2. to make-all ftiachnei tis archikes rithmissis gia to device
// 3. meta sindeete me ton server gia na parei sxetikes rithimissis pou echoun sxessi me to interconection
//	edo tora ean paei kati strava kai gia na min chasoume to devices, stelnei to log se enan poro
//	deverror
// 	kai apothikenonte ta lathi edo
// 4. gia na leitourgissi afto sosta prepei tora 
//	na ginei enas poros opou o christis tha enimeronete amesa gia to lathos
// 	kai messo tou interfaces na vlepei to log 
//	apo ton pinaka gia na mporesei na dossi lissi monos tou
//	h
//	se synergassia me ton admin
//	ean den einai o idios
// ******************************************** info ************************************
$error_clients = <<<EOD
 	CREATE TABLE error_clients (
	 	date VARCHAR(36) NOT NULL,
	 	client_id VARCHAR(80),
	 	error VARCHAR(2000),
	 	action VARCHAR(30),
	 	PRIMARY KEY (date)
 	);
EOD;
$db->exec($error_clients);
// *********************************************************** clients ************************************

$db->exec('PRAGMA encoding="UTF-8";');

chmod($dbfile, 0777);

