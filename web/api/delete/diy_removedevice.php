<?php
header("Content-Type: text/html; charset=utf-8");
/**
*
* @SWG\Resource(
*   apiVersion="0.1",
*   swaggerVersion="2.0",
*   basePath="https://arduino.os.cs.teiath.gr/api",
*   resourcePath="/device",
*   description="remove device",
*   produces="['application/json']"
* )
*/
/**
 * @SWG\Api(
 *   path="/device",
 *   @SWG\Operation(
 *     method="DELETE",
 *     summary="remove device",
 *     notes="remove device",
 *     type="removedevice",
 *     nickname="remove_device",
 *     @SWG\Parameter(
 *       name="access_token",
 *       description="access_token",
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
 *     @SWG\ResponseMessage(code=200, message="Επιτυχία", responseModel="Success"),
 *     @SWG\ResponseMessage(code=500, message="Αποτυχία", responseModel="Failure")
 *   )
 * )
 *
     */


 /**
 *
 * @SWG\Model(
 *              id="removedevice",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="device",type="string",description="device name"),
 *                  @SWG\Property(name="status",type="string",description="status of device private/org/public")
 * )
 */
//api/get/diy_Adddevice.php
// post device for add 
// access_token device org
$app->delete('/device', function () use ($authenticateForRole, $diy_storage)  {
        global $app;
        $params = loadParameters();
        $server = $authenticateForRole();
        $dbstorage = $diy_storage();
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
                $server->getResponse()->send();
                die;
        }else{
                $crypto_token = OAuth2\Request::createFromGlobals()->query["access_token"];
                $separator = '.';
                list($header, $payload, $signature) = explode($separator, $crypto_token);
                //echo base64_decode($payload);
                $params["payload"] = $payload;
                $params["storage"] = $dbstorage;
                $result = diy_removedevice(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
        	//$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_removedevice($payload,$storage){
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
    $device = OAuth2\Request::createFromGlobals()->query["device"];
    $diy_error["post"]["device"] = $device;
    $post["device"] = $device;  		// to client_id tou device			oauth_devices	oauth_clients	oauth_public_keys

	//$result["result"]["up"] =  $up;
	$gump = new GUMP();
	$gump->validation_rules(array(
		'device'    => 'required|alpha_numeric'
	));
	$gump->filter_rules(array(
		'device'    => 'trim|sanitize_string'
	));
	$validated = $gump->run($post);
	if($validated === false) {
		$result["parse_errors"] = $gump->get_readable_errors(true);
		$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
	}else{

                $dev = $storage->prepare('SELECT * FROM oauth_devices WHERE device  = :device');
                $dev->execute(array('device' => trim($device)));
                $rowdev = $dev->fetch(PDO::FETCH_ASSOC);
                if($rowdev){
			$org=$rowdev["organisation"];
		}else{
			$result["result"]["error"] =  ExceptionMessages::DeviceNotExist." , ". ExceptionCodes::DeviceNotExist;
		}
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
			//$result["result"]["error"] =  ExceptionMessages::DeviceExist." , ". ExceptionCodes::DeviceExist;
			$orgdeviceexists="yes";
		}else{
			$result["result"]["error"] =  ExceptionMessages::DeviceNotExist." , ". ExceptionCodes::DeviceNotExist;
			$orgdeviceexists="no";
		}

		if( ($orgexists == "yes" && ($orgowner == "yes" || $orgadmin == "yes")) && $orgdeviceexists == "yes"){
		//}else{

		    try {

			// oauth_public_keys table
			$encryption_algorithm="RS256";
			$stmt5 = $storage->prepare('DELETE from oauth_public_keys where client_id = :client_id');
			$stmt5->execute(array( 'client_id' => $device ));

			$stmt1 = $storage->prepare('SELECT * from oauth_clients where client_id = :client_id');
			$stmt1->execute(array('client_id' => $device));
			$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
			if($row1){

				$dataport = $row1["dataport"];	
				$apiport = $row1["apiport"];	
				// oauth_users table
				$user_id = $row1["user_id"];	
				$stmt = $storage->prepare('DELETE from oauth_users where user_id = :user_id');
				$stmt->execute(array('user_id' => $user_id));

				// oauth_ports table
				$stmt2 = $storage->prepare('DELETE from oauth_ports where port = :port');
				$stmt2->execute(array('port' => $dataport));
				$stmt2 = $storage->prepare('DELETE from oauth_ports where port = :port');
				$stmt2->execute(array('port' => $apiport));

				// oauth_clients table
				$stmt1 = $storage->prepare('DELETE from oauth_clients where client_id = :client_id');
				$stmt1->execute(array('client_id' => $device));

				// oauth_devices table
				$stmt11 = $storage->prepare('DELETE from oauth_devices where device = :device');
				$stmt11->execute(array('device' => $device));
			}

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

