<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/wssdeviceAccess
// get info for client (device) 
// ean o christis echei prosvassi se afto i ochi
// this is for onsubscribeconnections
$app->get('/wssdeviceAccess', function () use ($authenticateForRole, $diy_storage)  {
        global $app;
        $params = loadParameters();
        $server = $authenticateForRole();
        $dbstorage = $diy_storage();
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
                echo 'Unable to verify access token: '."\n";
                $server->getResponse()->send();
                die;
        }else{
                $crypto_token = OAuth2\Request::createFromGlobals()->query["access_token"];
                $separator = '.';
                list($header, $payload, $signature) = explode($separator, $crypto_token);
                //echo base64_decode($payload);
                $params["payload"] = $payload;
                $params["storage"] = $dbstorage;
                $result = diy_wssdeviceAccess(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_wssdeviceAccess($payload,$storage){
    global $app;

	$post["session"] = OAuth2\Request::createFromGlobals()->request["session"];
	$post["wss_user"] = OAuth2\Request::createFromGlobals()->request["wss_user"];
	$post["device"] = OAuth2\Request::createFromGlobals()->request["device"];
	$gump = new GUMP();
	$gump->validation_rules(array(
		'wss_user'    => 'required|alpha_numeric',
		'device'    => 'required|alpha_numeric',
		'session'    => 'required|alpha_numeric'
	));
	$gump->filter_rules(array(
		'wss_user'    => 'trim|sanitize_string',
		'device'    => 'trim|sanitize_string',
		'session'    => 'trim|sanitize_string'
	));
	//$result["gump2"] = $validated; // validation successful
	$result["controller"] = __FUNCTION__;
	$result["function"] = substr($app->request()->getPathInfo(),1);
	$result["method"] = $app->request()->getMethod();
	$params = loadParameters();
	$result->function = substr($app->request()->getPathInfo(),1);
	$result->method = $app->request()->getMethod();
	$params = loadParameters();
	$up=json_decode(base64_decode($payload));
	$client_id=$up->client_id;

	$result["result"]["view"]=  0;
	$validated = $gump->run($post);
	if($validated === false) {
		$result["parse_errors"] = $gump->get_readable_errors(true);
		$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
	} else {


		try {
			$stmt = $storage->prepare('SELECT * FROM oauth_devices WHERE device = :device');
			$stmt->execute(array('device' => $post["device"]));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if($row["organisation"]){
				$organisation=$row["organisation"];
				$organisation=$row["scope"];
				// o user einai sto scope
				try {
					$stmt1 = $storage->prepare('SELECT * FROM oauth_https_wss WHERE wss_user = :wss_user and session = :session');
					$stmt1->execute(array('wss_user' => trim($post["wss_user"]), 'session' => trim($post["session"])));
					$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
					if($row1["client_id"]){
						$client_user = $row1["client_id"];
						try {
							$stmt2 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_user');
							$stmt2->execute(array('wss_user' => trim($client_user)));
							$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
							if($row2["scope"]){
								$devview=$rganisation."_view";
								if (strpos($devview,$row2["scope"]) !== false) {
									$result["result"]["view"]=  1;
								}
							}
						} catch (Exception $e) {
							echo "error ".$e->getCode();
						}
					}else{
						echo "-----2----------".$row1["client_id"];
					}
				} catch (Exception $e) {
					echo "error ".$e->getCode();
				}
				// to devices einai public
				try {
					$stmt3 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_user');
					$stmt3->execute(array('client_user' => trim($client_user)));
					$row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
					if($row3["scope"]){
						$devview3=$rganisation."_dpub";
						if (strpos($devview3,$row3["scope"]) !== false) {
							$result["result"]["view"]=  1;
						}
					}
				} catch (Exception $e) {
					echo "error ".$e->getCode();
				}

			}
			//result_messages===============================================================      
			$result["status"] = "200";
			$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
		} catch (Exception $e) {
			$result["status"] = $e->getCode();
			$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
		}
	}
	return $result;

}

