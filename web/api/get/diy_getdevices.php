<?php

header("Content-Type: text/html; charset=utf-8");

/**
*
* @SWG\Resource(
*   apiVersion="0.1",
*   swaggerVersion="2.0",
*   basePath="https://arduino.os.cs.teiath.gr/api",
*   resourcePath="/devices",
*   description="list user devices",
*   produces="['application/json']"
* )
*/
/**
 * @SWG\Api(
 *   path="/devices",
 *   @SWG\Operation(
 *     method="GET",
 *     summary="List user devices",
 *     notes="Επιστρέφει user devices",
 *     type="pathModel",
 *     nickname="find_catalog",
 *     @SWG\Parameter(
 *       name="path",
 *       description="Πληκτρολογηστε το system path",
 *       required=false,
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
 *              id="pathModel",
 *              required="path",
 *                  @SWG\Property(name="path",type="string",description="Το path")
 * )
 */

//api/get/diy_getdevices.php
$app->get('/devices', function () use ($authenticateForRole, $diy_storage)  {
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
                $result = diy_getdevices(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});

function diy_getdevices($payload,$storage){
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
	$stmt2 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id');
	$stmt2->execute(array('client_id' => trim($client_id)));
	$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
	if($row2["scope"]){
		$scopes = explode(' ',trim($row2["scope"]));
		$diy_error["scopescount"]=count($scopes);
		$devview="view";
		$nr=0;
		for ($i = 0; $i <= count($scopes); $i++) { 
			$diy_error["scopes"]=$scopes[$i];
			$view = explode('_',$scopes[$i]);
			if (trim($view[1]) == $devview) {
			    $org = trim($view[0]);
			    $diy_error["org"]=$org;
// id edo ean to device einai dpri kai o user den einai o owner den prochorame
			    try {
				$stmt = $storage->prepare('SELECT * FROM oauth_devices WHERE organisation = :org');
				$stmt->execute(array('org' => $org));
				while($row = $stmt->fetch(PDO::FETCH_ASSOC)){ 
	    				$diy_error["dev"][$nr]=$row["device"];
					$devices["dev"][$nr]["device"]= $row["device"];
					$devices["dev"][$nr]["device_desc"]= $row["device_desc"];
					$devices["dev"][$nr]["organisation"]= $row["organisation"];
					$nr++;
				}
			    } catch (Exception $e) {
			      	$diy_error["db"]= $e->getCode();
				$result["status"] = $e->getCode();
				$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
			    } 
				
			}
		}
		$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
		$result["status"] = "200";
		$result["result"]=  $devices;
	}
    } catch (Exception $e) {
	$diy_error["db"] = $e->getCode();
	$result["status"] = $e->getCode();
	$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }

        if(diyConfig::read('debug') == 1){
                $result["debug"]=$diy_error;
        }

    return $result;
    
}

?>
