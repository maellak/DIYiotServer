<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/diy_devsshkeys.php
//make user and ssh keys fro ssh -i connections
// periorismos epissis tou user na mporei mono na anigi post kai tipota allo
$app->post('/devsshkeys', function () use ($authenticateForRole, $diy_storage)  {
        global $app;
        $params = loadParameters();
        $server = $authenticateForRole();
        $dbstorage = $diy_storage();
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
                echo 'Unable to verify access token: '."\n";
                $server->getResponse()->send();
                die;
        }else{
                $crypto_token = OAuth2\Request::createFromGlobals()->request["access_token"];
                $separator = '.';
                list($header, $payload, $signature) = explode($separator, $crypto_token);
                //echo base64_decode($payload);
                $params["payload"] = $payload;
                $params["storage"] = $dbstorage;
                $result = diy_devsshkeys(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
        	//$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_devsshkeys($payload,$storage){
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
    try {
        $public_key = OAuth2\Request::createFromGlobals()->request["public_key"];
        $public_key = trim($public_key);
        $post["public_key"] = OAuth2\Request::createFromGlobals()->request["public_key"];


	$post["public_key"] = urldecode($post["public_key"]);

	$gump = new GUMP();
	$gump->validation_rules(array(
	  'public_key'    => 'required|alpha_numeric'
	));
	$gump->filter_rules(array(
	  'public_key'    => 'trim|sanitize_string'
	));
	$validated = $gump->run($post);
	if($validated === false) {
        	$result["gump1"] = $gump->get_readable_errors(true);
	} else {
        	$result["gump2"] = $validated; // validation successful
	}
	$stmt1 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id');
        $stmt1->execute(array('client_id' => $client_id));
	 foreach ($stmt1 as $row) {
		$sshport= $row["sshport"];
		$apiport= $row["apiport"];
		$dataport= $row["dataport"];
	 }

	$pos  = mb_strripos($public_key, ' ');
	$s = 0; 
	$public = mb_substr($public_key, $s, $pos); 
        $stmt = $storage->prepare('UPDATE oauth_devices set public_key=:public_key where device=:client_id');
        $stmt->execute(array('client_id' => $client_id, 'public_key' => $public_key));
	//result_messages===============================================================      
	$auth_settings = 'no-pty,no-X11-forwarding,permitopen="localhost:'.$dataport.'",permitopen="localhost:'.$apiport.'",command="/bin/echo do-not-send-commands" '.$public.' '.$client_id.'=@OpenWrt';

	//file_put_contents('../tmp/authorized_keys', $auth_settings);
        //$result["result"]=  $auth_settings.$public_key;
        $result["result"]=  "ok";
        $result["error"]=  $error;
        $result["status"] = "200";
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }
    return $result;

}

