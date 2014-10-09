<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/diy_getinfoconfig.php
// get info for client (device) 
// the device request config info for device settings not the owner 
// this is for devices configuration and sync
$app->get('/devinfo', function () use ($authenticateForRole, $diy_storage)  {
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
                $result = diy_getinfoconfig(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_getinfoconfig($payload,$storage){
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    $params = loadParameters();
    $up=json_decode(base64_decode($payload));
    $client_id=$up->client_id;
    try {
        $stmt = $storage->prepare('SELECT * FROM oauth_devices WHERE client_id = :client_id');
        $stmt->execute(array('client_id' => $client_id));
        //device, device_desc, organisation, client_id
        $nr=0;
                foreach ($stmt as $row) {
                        $devices["dev"][$nr]["device"]= $row["device"];
                        $devices["dev"][$nr]["device_desc"]= $row["device_desc"];
                        $devices["dev"][$nr]["organisation"]= $row["organisation"];
                        $nr++;
                }
//result_messages===============================================================      
        $result["result"]=  $devices;
        $result["status"] = "200";
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }
    return $result;

}

