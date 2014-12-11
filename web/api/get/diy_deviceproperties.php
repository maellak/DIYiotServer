<?php
header("Content-Type: text/html; charset=utf-8");
/**
*
* @SWG\Resource(
*   apiVersion="0.1",
*   swaggerVersion="2.0",
*   basePath="https://arduino.os.cs.teiath.gr/api",
*   resourcePath="/deviceproperties",
*   description="device properties status/mode",
*   produces="['application/json']"
* )
*/
/**
 * @SWG\Api(
 *   path="/deviceproperties",
 *   @SWG\Operation(
 *     method="GET",
 *     summary="device properties",
 *     notes="device properties status/mode",
 *     type="deviceproperties",
 *     nickname="device_properties",
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
 *     @SWG\Parameter(
 *       name="status",
 *       description="change status to private/org/public ",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="mode",
 *       description="change mode to devel/production ",
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
 *              id="deviceproperties",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="device",type="string",description="device name"),
 *                  @SWG\Property(name="status",type="string",description="status of device ")
 * )
 */
//api/get/diy_Adddevice.php
// post device for add 
// access_token device org
$app->get('/deviceproperties', function () use ($authenticateForRole, $diy_storage)  {
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
                $result = diy_deviceproperties(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
        	//$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_deviceproperties($payload,$storage){
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
    $status = OAuth2\Request::createFromGlobals()->query["status"];
    $mode = OAuth2\Request::createFromGlobals()->query["mode"];
    $diy_error["post"]["device"] = $device;
    $diy_error["post"]["status"] = $status;
    $diy_error["post"]["mode"] = $mode;
    $post["device"] = $device;  		// to client_id tou device			oauth_devices	oauth_clients	oauth_public_keys
    $post["status"] = $status;  		// to client_id tou device			oauth_devices	oauth_clients	oauth_public_keys
    $post["mode"] = $mode;  		// to client_id tou device			oauth_devices	oauth_clients	oauth_public_keys

	//$result["result"]["up"] =  $up;
	$gump = new GUMP();
	$gump->validation_rules(array(
		'device'    => 'required|alpha_numeric',
		'status'    => 'required|alpha_numeric',
		'mode'    => 'required|alpha_numeric'
	));
	$gump->filter_rules(array(
		'device'    => 'trim|sanitize_string',
		'status'    => 'trim|sanitize_string',
		'mode'    => 'trim|sanitize_string'
	));
	$validated = $gump->run($post);
	if($validated === false) {
		$result["parse_errors"] = $gump->get_readable_errors(true);
		$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
	}else{

		$deviceproperties = "no";
		$orgscopeadmin="no";
                $dev = $storage->prepare('SELECT * FROM oauth_devices WHERE device  = :device');
                $dev->execute(array('device' => trim($device)));
                $rowdev = $dev->fetch(PDO::FETCH_ASSOC);
                if($rowdev){
			$org=$rowdev["organisation"];
                        $devclient_id=$rowdev["client_id"];
                        $statust=$rowdev["status"];
                        $modet=$rowdev["mode"];
		}else{
			$result["result"]["error"] =  ExceptionMessages::DeviceNotExist." , ". ExceptionCodes::DeviceNotExist;
		}

		function check($storage, $userscopes, $org, $client_id, $device){
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
					$result["result"]["check"] =  "ok";
					return $result;
				}else{
					$result["result"]["check"] =  "no";
					return $result;
				}
			}

			if($statust =="private" && $devclient_id == $client_id){
				$deviceproperties="yes";
			}
			if( $statust =="org" || $statust =="public" ){
				$diy_error["error"]["check"] =  check($storage, $userscopes, $org, $client_id, $device);
				// check if user owned the devices or have admin scope in orgfrom
				$checkr =  check($storage, $userscopes, $org, $client_id, $device);

				if( $checkr["result"]["check"] == "ok"){
					$diy_error["error"]["org"] =  "ok";
					$deviceproperties = "yes";
				}
			}
		//if( ($orgexists == "yes" && ($orgowner == "yes" || $orgadmin == "yes")) && $orgdeviceexists == "yes"){
		if( $deviceproperties == "yes"){
		//}else{

		    try {

			if($mode == "devel"){
				$modet = "devel";
				$scopedev1 = $org."_devel";
			}elseif($mode == "production"){
				$modet = "production";
				$scopedev1 = $org."_production";
			}

			if($status == "private"){
				$statust = "private";
				$scopedev2 = $org."_dpri";
			}elseif($status == "org"){
				$statust = "org";
				$scopedev2 = $org."_org";
			}elseif($status == "public"){
				$statust = "public";
				$scopedev2 = $org."_dpub";
			}
			$scopedev = $scopedev1." ".$scopedev2;
			// oauth_clients table
			$stmt1 = $storage->prepare('UPDATE oauth_clients set scope = :scopedev where client_id = :client_id');
			$stmt1->execute(array('client_id' => $device, 'scopedev' => $scopedev));

			// oauth_devices table
			$stmt11 = $storage->prepare('UPDATE oauth_devices set status = :statust, mode = :modet  where device = :device');
			$stmt11->execute(array('device' => $device, 'statust' => $statust, 'modet' => $modet));

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

