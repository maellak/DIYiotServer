<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/diy_Adddevice.php
// post device for add 
// access_token device org
$app->post('/adddevice', function () use ($authenticateForRole, $diy_storage)  {
        global $app;
        $params = loadParameters();
        $server = $authenticateForRole();
        $dbstorage = $diy_storage();
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
                $server->getResponse()->send();
                die;
        }else{
                $crypto_token = OAuth2\Request::createFromGlobals()->request["access_token"];
                $separator = '.';
                list($header, $payload, $signature) = explode($separator, $crypto_token);
                //echo base64_decode($payload);
                $params["payload"] = $payload;
                $params["storage"] = $dbstorage;
                $result = diy_adddevice(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
        	//$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_adddevice($payload,$storage){
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    //$params = loadParameters();
    $up=json_decode(base64_decode($payload));
    $client_id=$up->client_id;
//'INSERT INTO oauth_clients (client_id, client_secret, user_id, scope) VALUES ("testdev", "arduinodev#c%cf!q","2","test_dpri test_dev")');
    $org = OAuth2\Request::createFromGlobals()->request["org"];
    $device = OAuth2\Request::createFromGlobals()->request["device"];
    $devproperties = OAuth2\Request::createFromGlobals()->request["properties"];
    $client_secret = OAuth2\Request::createFromGlobals()->request["passwd"];
    $device_desc = OAuth2\Request::createFromGlobals()->request["device_desc"];
    $diy_error["post"]["org"] = $org;
    $diy_error["post"]["device"] = $device;
    $diy_error["post"]["client_secret"] = $client_secret;
    $diy_error["post"]["devproperties"] = $devproperties;
    $diy_error["post"]["device_desc"] = $device_desc;
    $post["org"] = $org;			//organisation					oauth_devices	
    $post["device"] = $device;  		// to client_id tou device			oauth_devices	oauth_clients	oauth_public_keys
    $post["devproperties"] = $devproperties;	
    $post["device_desc"] = $device_desc;	//mia perigrafi oti thelei o christis		oauth_devices

    $tempfile=tempnam(sys_get_temp_dir(),'');
    if (file_exists($tempfile)) { unlink($tempfile); }
    mkdir($tempfile);
    if (is_dir($tempfile)) { 
    	$exec("openssl genrsa -out $tempfile/$client_id-privkey.pem 2048");
    	$exec("openssl rsa -in $tempfile/$client_id-privkey.pem -pubout -out $tempfile/$client_id-pubkey.pem");
	$publicKey  = file_get_contents("$tempfile/$client_id-pubkey.pem");
	$privateKey = file_get_contents("$tempfile/$client_id-privkey.pem");
    }



    $post["public_key"] = $publicKey;		//mia perigrafi oti thelei o christis		oauth_devices	oauth_clients	oauth_public_keys
    $post["private_key"] = $publicKey;		//mia perigrafi oti thelei o christis		oauth_devices	oauth_clients	oauth_public_keys
    $post["encryption_algorithm"] = 'RS256'	;//mia perigrafi oti thelei o christis						oauth_public_keys

    $post["public_key_active"] = $device_desc;	//mia perigrafi oti thelei o christis		oauth_devices

    $post["port"] = $device_desc;		//port gia to device				oauth_ports	oauth_clients dataport, apiport
    $post["client_id"] = $device_desc;		//se pion aniki					oauth_ports	oauth_clients

    $post["dataport"] = $devpasswd;		//dataport							oauth_clients
    $post["apiport"] = $devpasswd;		//apiport							oauth_clients
    $post["apihost"] = $devpasswd;		//apihost							oauth_clients
    $post["sshhost"] = $devpasswd;		//sshhost							oauth_clients
    $post["sshport"] = $devpasswd;		//sshport							oauth_clients
/*
		user_id int oauth_users

              client_id VARCHAR(80),
                public_key VARCHAR(2000),
                private_key VARCHAR(2000),
                encryption_algorithm VARCHAR(100) DEFAULT 'RS256'

		$db->exec('UPDATE oauth_clients set dataport="50000", apiport="50001", apihost="https://arduino.os.cs.teiath.gr", sshhost="arduino.os.cs.teiath.gr", sshport="9999" where client_id="testdev"');
		oauth_ports (port, client_id) VALUES ("50000","testdev")');

                device VARCHAR(80) NOT NULL,
                device_desc TEXT NOT NULL,
                organisation TEXT NOT NULL,
                client_id VARCHAR(80) NOT NULL,
                public_key VARCHAR(2000),
                public_key_active VARCHAR(10),
*/
        $gump = new GUMP();
        $gump->validation_rules(array(
                'org'    => 'required|alpha_numeric',
                'device'    => 'required|alpha_numeric',
                'client_secret'    => 'required|max_len,100|min_len,6',
                'devproperties'    => 'required|alpha_numeric',
                'device_desc'    => 'required|max_len,100|alpha_dash'
        ));
        $gump->filter_rules(array(
                'org'    => 'trim|sanitize_string',
                'device'    => 'trim|sanitize_string',
                'client_secret'    => 'trim',
                'devproperties'    => 'trim|sanitize_string',
                'device_desc'    => 'trim|sanitize_string'
        ));
        $validated = $gump->run($post);
        if($validated === false) {
                $result["parse_errors"] = $gump->get_readable_errors(true);
                $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
	}

    try {
	    $tempfile=tempnam(sys_get_temp_dir(),'');
	    if (file_exists($tempfile)) { unlink($tempfile); }
	    mkdir($tempfile);
	    if (is_dir($tempfile)) { 
		$exec("openssl genrsa -out $tempfile/$client_id-privkey.pem 2048");
		$exec("openssl rsa -in $tempfile/$client_id-privkey.pem -pubout -out $tempfile/$client_id-pubkey.pem");
		$publicKey  = file_get_contents("$tempfile/$client_id-pubkey.pem");
		$privateKey = file_get_contents("$tempfile/$client_id-privkey.pem");
	    }
	// user_id for dev
        $lastkey = $storage->query('SELECT user_id FROM oauth_users ORDER BY user_id DESC LIMIT 1');
        foreach($lastkey as $curRow) { $lastkey = intval($curRow[0]); }
	$lastkey++;
        $stmt = $storage->prepare('INSERT INTO oauth_users (user_id,email_ver_code) VALUES (:user_id,TRUE)');
        $stmt->execute(array('user_id' => $lastkey));

	$scope=$org."_dev";
	$scope .= $org."_dpri";
        $apiport = $storage->query('SELECT apiport FROM oauth_clients ORDER BY apiport DESC LIMIT 1');
        foreach($apiport as $curRow) { $apiport = intval($curRow[0]); }
	$dataport=$apiport + 1;
	$apiport=$apiport + 2;
	$apihost=diyConfig::read('api.host');
	$sshhost=diyConfig::read('ssh.host');
	$sshport=diyConfig::read('ssh.port');

        $stmt1 = $storage->prepare('INSERT INTO oauth_clients (client_id, client_secret, user_id, scope, dataport, apiport, apihost, sshhost, sshport) VALUES (:client_id, :client_secret, :user_id, :scope, :dataport, :apiport, :apihost, :sshhost, :sshport)');
        $stmt1->execute(array('user_id' => $lastkey, 'client_id' => $client_id, 'client_secret' => $client_secret, 'scope' => $scope, 'dataport' => $dataport, 'apiport' => $apiport, 'apihost' = > $apihost, 'sshhost' => $sshhost, 'sshport' => $sshport));

	$stmt1 = $storage->prepare('INSERT  FROM oauth_clients WHERE client_id = :device');
	$stmt1->execute(array('device' => trim($device)));
	$row = $stmt1->fetch(PDO::FETCH_ASSOC);
	if($row){
        	$result["result"]["error"] =  ExceptionMessages::DeviceExist." , ". ExceptionCodes::DeviceExist;
	}
	//result_messages===============================================================      
        $result["result"]["session"] =  $session;
        $result["error"]=  $error;
        $result["status"] = "200";
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }

    if(diyConfig::read('debug') == 1){
	$result["debug"]=$diy_error;
    }

    return $result;

}

