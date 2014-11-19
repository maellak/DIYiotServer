<?php

$dbfile = '../db/oauth.sqlite';
$HOST="you server";


if (!is_writable(__DIR__)) {
    if (!@chmod(__DIR__, 0777)) {
        throw new Exception("Unable to write to $dbfile");
    }
}

// rebuild the DB
$db = new PDO(sprintf('sqlite:%s', $dbfile));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// *********************************************************** clients ************************************
// gia na syndethi o christis chriasete toulachiston ta parakato
// username kai password kai scope

// ta keys gia ton christi


// CLIENT_ID
$publicKey  = file_get_contents('../ssh/CLIENT_ID_pubkey.pem');
$privateKey = file_get_contents('../ssh/CLIENT_ID_privkey.pem');
$db->exec('INSERT INTO oauth_clients (client_id, client_secret) VALUES ("CLIENT_ID", "ssssssssssssssssssssssssssssssssssssss")');
$db->exec('INSERT INTO oauth_public_keys (client_id, public_key, private_key, encryption_algorithm) VALUES ("CLIENT_ID", "'.$publicKey.'", "'.$privateKey.'", "RS256")');

// CLIENT_ID1 
$publicKey  = file_get_contents('../ssh/CLIENT_ID1_pubkey.pem');
$privateKey = file_get_contents('../ssh/CLIENT_ID1_privkey.pem');
$db->exec('INSERT INTO oauth_clients (client_id, client_secret, scope) VALUES ("CLIENT_ID1", "ssssssssssssssssssssssssssssssssssssss","main test_view")');
$db->exec('INSERT INTO oauth_public_keys (client_id, public_key, private_key, encryption_algorithm) VALUES ("CLIENT_ID1", "'.$publicKey.'", "'.$privateKey.'", "RS256")');

// this user is the main user for the test example  used in webclient 
$db->exec('INSERT INTO oauth_clients (client_id, client_secret, user_id, scope) VALUES ("CLIENT_ID11", "ssssssssssssssssssssssssssssssssssssss","1","test_admin main")'); 				// insert client user
$db->exec('INSERT INTO oauth_public_keys (client_id, public_key, private_key, encryption_algorithm) VALUES ("CLIENT_ID11", "'.$publicKey.'", "'.$privateKey.'", "RS256")');	// insert key gia client user
$db->exec('INSERT INTO oauth_users (user_id, email_verified) VALUES (1, 1)');   

// this user is the main user for the test example  used in wss   (put the user information in ws/src/MyApp/Config.php ) 
// wss
$db->exec('INSERT INTO oauth_clients (client_id, client_secret, user_id, scope) VALUES ("wssusername", "wsspassword","3","admin")');
$db->exec('INSERT INTO oauth_public_keys (client_id, public_key, private_key, encryption_algorithm) VALUES ("diywssconnect", "'.$publicKey.'", "'.$privateKey.'", "RS256")');

// *********************************************************** clients ************************************

// *********************************************************** organisation ************************************
// this is the main organisation used for the test example
	db->exec('INSERT INTO oauth_organisations (organisation, client_id) VALUES ("test","CLIENT_ID11")');										// insert org tou client user
$db->exec('INSERT INTO oauth_clients (client_id, client_secret, user_id, scope) VALUES ("testdev", "ssssssssssssssssssssssssssssssssssssss","2","test_dpri test_dev")'); 					// insert 
$db->exec('INSERT INTO oauth_users (user_id, email_verified) VALUES (2, 1)');   

// *********************************************************** organisation ************************************

// ***********************************************************devices ************************************
//devices testdev begin
// to key edo gia ta devs tha einai diaforetiko gia kathe dev   o client user tha echei to geniko   to key tou dev tha ginete download apo ton client user giati tha prpei na mpei sto dev gia auth giafto kai einai diaforetiko
$publicKeydev  = file_get_contents('../ssh/CLIENT_ID_pubkey.pem');
$privateKeydev = file_get_contents('../ssh/CLIENT_ID_privkey.pem');
// this is the main test device used for the test example
$db->exec('INSERT INTO oauth_public_keys (client_id, public_key, private_key, encryption_algorithm) VALUES ("testdev", "'.$publicKeydev.'", "'.$privateKeydev.'", "RS256")');		// insert dev key 
// to devices chriasete kai afto oti o client giati kai afto prepei na sindethi me ton server gia na stili ta data kai epissis na anixi socket gia na lamvanei entoles sxetika me tis leitourgies pou prepei ad-hoc na kanei
$db->exec('INSERT INTO oauth_devices (device, device_desc, organisation, client_id, status, mode) VALUES ("testdev","perigrafi","test","CLIENT_ID11","org","devel")');                                  // insert device device=client_id  perigrafi, organisation kai client_id tou user pou aniki to org
$db->exec('INSERT INTO oauth_devices (device, device_desc, organisation, client_id, status, mode) VALUES ("testdev1","perigrafi1","test","CLIENT_ID11","private","devel")'); 






//to port sti opoio tha steknei ta data
// ta ports pou echoun dothi ta kratame se enan pinaka
// prin dosoume ena neo port elenchoume ean einai eleftero ston server kai oti den echei dothei
// giafto iparchei aftos o pinakas
$db->exec('INSERT INTO oauth_ports (port, client_id) VALUES ("50000","testdev")');												// to port tou dev 
$db->exec('INSERT INTO oauth_ports (port, client_id) VALUES ("50001","testdev")');												// to port tou dev 
//to port sti opoio tha steknei ta dechete tis entoles
//enimerossi tou sxetikou pinaka
$db->exec('UPDATE oauth_clients set dataport="50000", apiport="50001", apihost="https://'.$HOST.'", sshhost="'.$HOST.'", sshport="9999" where client_id="testdev"');													// update tou port tou dev sto vasiko table ton cliebts
//devices testdev end
// ***********************************************************devices ************************************


// ***********************************************************scopes ************************************
// gia kathe organisation ginete auto insert 7 scopes    
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("test", "0")');		// scope gia to organisation mono o owner echei afto to scope
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("test_dev", "0")');	// scope gia to devices   mono to devices echei afto to scope
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("test_dpri", "0")');	// dev is private.
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("test_dpub", "0")');	// dev is public gia olous tous users pou einai sto systima

$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("test_view", "0")');	// scope gia users	mono view
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("test_devel", "0")');	// 	-"-		mono devel kai view
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("test_admin", "0")');	// 	-"-		ola devel view kai admin 

$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("main", "1")');		// default scope 
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("dpub", "0")');		// osa devs echoun afto to scope einai public genikos kai ochi mono stous user genikos tou systimatos
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("devel", "0")');	// o user poui echei afto to scope mporei na kanei mdevel ola ta devs olos ton orgs kai users
$db->exec('INSERT INTO oauth_scopes (scope, is_default) VALUES ("admin", "0")');	// o user poui echei afto to scope mporei na kanei admin ola ta devs olos ton orgs kai users
// ***********************************************************scopes ************************************

$db->exec('PRAGMA encoding="UTF-8";');

chmod($dbfile, 0777);
