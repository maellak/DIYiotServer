<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/diy_wssAddsession.php
// post wss session and wss user  and verify if exists
// if not create it
// ginete kai i sindessi me to token tou api
$app->post('/wssaddsession', function () use ($authenticateForRole, $diy_storage)  {
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
                $result = diy_wssaddsession(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
        	//$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_wssaddsession($payload,$storage){
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
    $session = OAuth2\Request::createFromGlobals()->request["session"];
    $wss_user = OAuth2\Request::createFromGlobals()->request["wss_user"];
    //$device = OAuth2\Request::createFromGlobals()->request["device"];
    try {
	$stmt1 = $storage->prepare('SELECT * FROM oauth_https_wss WHERE client_id = :client_id');
	$stmt1->execute(array('client_id' => trim($client_id)));
	$row = $stmt1->fetch(\PDO::FETCH_ASSOC);
	if($row){
		try {
			$stmt3 = $storage->prepare('UPDATE  oauth_https_wss set client_id = :client_id, wss_user = :wss_user, session = :session WHERE client_id = :client_id');
			$stmt3->execute(array('client_id' => $client_id, 'wss_user' => $wss_user, 'session' => $session));
		} catch (Exception $e) {
			echo "error 3".$e->getMessage();
		}
	}else{
		try {
			$stmt2 = $storage->prepare('INSERT INTO oauth_https_wss (client_id, wss_user, session) VALUES  (:client_id, :wss_user,  :session)');
			$stmt2->execute(array('client_id' => trim($client_id), 'wss_user' => $wss_user, 'session' => $session));
		} catch (Exception $e) {
			echo "error 2".$e->getCode();
		}
	}
	//result_messages===============================================================      
        $result["result"]["session"] =  $session;
        $result["result"]["client_id"]= $client_id;

        $result["error"]=  $error;
        $result["status"] = "200";
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }
    return $result;

}

