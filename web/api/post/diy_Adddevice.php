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
    $devpasswd = OAuth2\Request::createFromGlobals()->request["passwd"];
    $device_desc = OAuth2\Request::createFromGlobals()->request["device_desc"];
    $diy_error["post"]["org"] = $org;
    $diy_error["post"]["device"] = $device;
    $diy_error["post"]["devpasswd"] = $devpasswd;
    $diy_error["post"]["devproperties"] = $devproperties;
    $diy_error["post"]["device_desc"] = $device_desc;
    $post["org"] = $org;
    $post["device"] = $device;
    $post["devpasswd"] = $devpasswd;
    $post["devproperties"] = $devproperties;
    $post["device_desc"] = $device_desc;
        $gump = new GUMP();
        $gump->validation_rules(array(
                'org'    => 'required|alpha_numeric',
                'device'    => 'required|alpha_numeric',
                'devpasswd'    => 'required|max_len,100|min_len,6',
                'devproperties'    => 'required|alpha_numeric',
                'device_desc'    => 'required|max_len,100|alpha_dash'
        ));
        $gump->filter_rules(array(
                'org'    => 'trim|sanitize_string',
                'device'    => 'trim|sanitize_string',
                'devpasswd'    => 'trim',
                'devproperties'    => 'trim|sanitize_string',
                'device_desc'    => 'trim|sanitize_string'
        ));
        $validated = $gump->run($post);
        if($validated === false) {
                $result["parse_errors"] = $gump->get_readable_errors(true);
                $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
	}

    try {
	$stmt1 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :device');
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

