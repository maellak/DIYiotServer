<?php
header("Content-Type: text/html; charset=utf-8");
/**
*
* @SWG\Resource(
*   apiVersion="0.1",
*   swaggerVersion="2.0",
*   basePath="https://arduino.os.cs.teiath.gr/api",
*   resourcePath="/adddevice",
*   description="Add device",
*   produces="['application/json']"
* )
*/
/**
 * @SWG\Api(
 *   path="/adddevice",
 *   @SWG\Operation(
 *     method="POST",
 *     summary="Add device in a organisation",
 *     notes="Create device in organisation kai epistrefei tis schetikes plirofories. <br>To Organisation prepei na yparchei kai o christis na einai o owner i na aniki sto Organisations admin scope",
 *     type="adddevice",
 *     nickname="add_device",
 *     @SWG\Parameter(
 *       name="access_token",
 *       description="access_token",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="org",
 *       description="organisation gia to device",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="device",
 *       description="device name (alphanumeric)",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="device_desc",
 *       description="description ",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="password",
 *       description="password for the devices",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\ResponseMessage(code=200, message="Επιτυχία", responseModel="Success"),
 *     @SWG\ResponseMessage(code=500, message="Αποτυχία", responseModel="Failure")
 *   )
 * )
 *
     */


 /**
 *
 * @SWG\Model(
 *              id="adddevice",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="org",type="string",description="organisation pou aniki to device"),
 *                  @SWG\Property(name="device",type="string",description="device name"),
 *                  @SWG\Property(name="device_desc",type="string",description="device desc"),
 *                  @SWG\Property(name="status",type="string",description="status of device private/org/public"),
 *                  @SWG\Property(name="mode",type="string",description="mode of device devel/production")
 * )
 */
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
    $userscope=$up->scope;
    $org = OAuth2\Request::createFromGlobals()->request["org"];
    $device = OAuth2\Request::createFromGlobals()->request["device"];
    $client_secret = OAuth2\Request::createFromGlobals()->request["passwd"];
    $device_desc = OAuth2\Request::createFromGlobals()->request["device_desc"];
    $diy_error["post"]["org"] = $org;
    $diy_error["post"]["device"] = $device;
    $diy_error["post"]["client_secret"] = $client_secret;
    $diy_error["post"]["device_desc"] = $device_desc;
    $post["org"] = $org;			//organisation					oauth_devices	
    $post["device"] = $device;  		// to client_id tou device			oauth_devices	oauth_clients	oauth_public_keys
    $post["client_secret"] = $client_secret;	//mia perigrafi oti thelei o christis		oauth_devices
    $post["device_desc"] = $device_desc;	//mia perigrafi oti thelei o christis		oauth_devices

	//$result["result"]["up"] =  $up;
	$gump = new GUMP();
	$gump->validation_rules(array(
		'org'    => 'required|alpha_numeric',
		'device'    => 'required|alpha_numeric',
		'client_secret'    => 'required|max_len,100|min_len,6',
		'device_desc'    => 'required|max_len,100'
	));
	$gump->filter_rules(array(
		'org'    => 'trim|sanitize_string',
		'device'    => 'trim|sanitize_string',
		'client_secret'    => 'trim',
		'device_desc'    => 'trim|sanitize_string'
	));
	$validated = $gump->run($post);
	if($validated === false) {
		$result["parse_errors"] = $gump->get_readable_errors(true);
		$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
	}else{

                //check if org name exists
		$orgexists = "no";
                $stmtorg = $storage->prepare('SELECT * FROM oauth_organisations WHERE organisation = :org');
                $stmtorg->execute(array('org' => trim($org)));
                $roworg = $stmtorg->fetch(PDO::FETCH_ASSOC);
                if($roworg){
			$orgexists = "yes";
                        //$result["result"]["error"] =  ExceptionMessages::OrgExist." , ". ExceptionCodes::OrgExist;

			$orgadmin="no";		
			$orgowner="no";
			$userscopes = explode(' ',trim($userscope));
			$orgscope=$org."_admin";
			for ($i = 0; $i <= count($userscopes); $i++) {
				if (trim($userscopes[$i]) == $orgscope) {
					$orgadmin="yes";		
				}

			}
			if($orgadmin == "no"){
				//check if org name exists and client_id
				$stmtorg1 = $storage->prepare('SELECT * FROM oauth_organisations WHERE organisation = :org and client_id = :client_id');
				$stmtorg1->execute(array('org' => trim($org), 'client_id' => $client_id));
				$roworg1 = $stmtorg1->fetch(PDO::FETCH_ASSOC);
				if(!$roworg1){
					$result["result"]["error"] =  ExceptionMessages::OrgOwner." , ". ExceptionCodes::OrgOwner;
				}else{
					$orgowner="yes";
				}
			}
                }else{
                        $result["result"]["error"] =  ExceptionMessages::OrgNotExist." , ". ExceptionCodes::OrgNotExist;
		}

		//check if device name exists
		$orgdeviceexists="no";
		$stmt = $storage->prepare('SELECT client_id  FROM oauth_clients WHERE client_id = :device');
		$stmt->execute(array('device' => trim($device)));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if($row){
			$result["result"]["error"] =  ExceptionMessages::DeviceExist." , ". ExceptionCodes::DeviceExist;
			$orgdeviceexists="yes";
		}

		if( ($orgexists == "yes" && ($orgowner == "yes" || $orgadmin == "yes")) && $orgdeviceexists == "no"){
		//}else{

		    try {
			$tempfile=tempnam('tmp/','');
			if (file_exists($tempfile)) { unlink($tempfile); }
			    mkdir($tempfile);
			    if (is_dir($tempfile)) { 
				exec("openssl genrsa -out $tempfile/$client_id-privkey.pem 2048");
				exec("openssl rsa -in $tempfile/$client_id-privkey.pem -pubout -out $tempfile/$client_id-pubkey.pem");
				$publicKey  = file_get_contents("$tempfile/$client_id-pubkey.pem");
				$privateKey = file_get_contents("$tempfile/$client_id-privkey.pem");

				// oauth_public_keys table
				$encryption_algorithm="RS256";
				$stmt5 = $storage->prepare('INSERT INTO oauth_public_keys (client_id, public_key, private_key, encryption_algorithm) VALUES (:client_id, :public_key, :private_key, :encryption_algorithm)');
				$stmt5->execute(array( 'client_id' => $device, 'public_key' => $publicKey, 'private_key' => $privateKey, ':encryption_algorithm' => $encryption_algorithm));
				unlink("$tempfile/$client_id-pubkey.pem");
				unlink("$tempfile/$client_id-privkey.pem");
			    // na ftiaxo to key me tis portes na einai etoimo
			    // tha to kano messo cron
			    // o pinakas ta echei ola oauth_clients
			    }

			// user_id for dev
			$lastkey = $storage->query('SELECT user_id FROM oauth_users ORDER BY user_id DESC LIMIT 1');
			foreach($lastkey as $curRow) { $lastkey = intval($curRow[0]); }
			$lastkey++;
			// oauth_users table
			$stmt = $storage->prepare('INSERT INTO oauth_users (user_id,email_verified) VALUES (:user_id,"1")');
			$stmt->execute(array('user_id' => $lastkey));

			$scope=$org."_dev";
			$scope .= ' '.$org."_dpri";
			$apiport = $storage->query('SELECT apiport FROM oauth_clients ORDER BY apiport DESC LIMIT 1');
			foreach($apiport as $curRow) { $apiport = intval($curRow[0]); }
			$dataport=$apiport + 1;
			$apiport=$apiport + 2;
			$apihost=diyConfig::read('api.host');
			$sshhost=diyConfig::read('ssh.host');
			$sshport=diyConfig::read('ssh.port');

			// oauth_ports table
			$stmt2 = $storage->prepare('INSERT INTO oauth_ports (port, client_id) VALUES (:port, :client_id)');
			$stmt2->execute(array('client_id' => $device, 'port' => $dataport));
			$stmt2 = $storage->prepare('INSERT INTO oauth_ports (port, client_id) VALUES (:port, :client_id)');
			$stmt2->execute(array('client_id' => $device, 'port' => $apiport));

			// oauth_clients table
			$tty="/dev/ttyACM0";
			$baud="115200";
			$stmt1 = $storage->prepare('INSERT INTO oauth_clients (client_id, client_secret, user_id, scope, dataport, apiport, apihost, sshhost, sshport, tty, baud) VALUES (:client_id, :client_secret, :user_id, :scope, :dataport, :apiport, :apihost, :sshhost, :sshport, :tty, :baud)');
			$stmt1->execute(array('user_id' => $lastkey, 'client_id' => $device, 'client_secret' => $client_secret, 'scope' => $scope, 'dataport' => $dataport, 'apiport' => $apiport, 'apihost' => $apihost, 'sshhost' => $sshhost, 'sshport' => $sshport, 'tty' => $tty, 'baud' => $baud));

			// oauth_devices table
			$public_key_active="yes";
			$status="private";
			$mode="devel";
			$stmt11 = $storage->prepare('INSERT INTO oauth_devices (device, device_desc, organisation, client_id, public_key_active, status, mode) VALUES (:device, :device_desc, :organisation, :client_id, :public_key_active, :status, :mode)');
			$stmt11->execute(array('device' => $device, 'client_id' => $client_id, 'device_desc' => $device_desc, 'organisation' => $org, 'public_key_active' => $public_key_active, 'status' => $status, 'mode' => $mode));


    			$post["status"] = $status;	
    			$post["mode"] = $mode;	
			//result_messages===============================================================      
			$result["result"]["result"] =  $post;
			$result["result"]["session"] =  $session;
			$result["error"]=  $error;
			$result["status"] = "200";
			$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
		    } catch (Exception $e) {
			$result["status"] = $e->getCode();
			$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
		    }
		}
	}
    if(diyConfig::read('debug') == 1){
	$result["debug"]=$diy_error;
    }

    return $result;

}

