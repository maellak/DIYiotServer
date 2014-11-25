<?php

header("Content-Type: text/html; charset=utf-8");
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

/**
*
* @SWG\Resource(
*   apiVersion="0.1",
*   swaggerVersion="2.0",
*   basePath="https://arduino.os.cs.teiath.gr/api",
*   resourcePath="/diyexec",
*   description="Gateway for command exec in devices",
*   produces="['application/json']"
* )
*/

/**
 * @SWG\Api(
 *   path="/diyexec",
 *   @SWG\Operation(
 *     method="POST",
 *     summary="Gateway for command exec in devices",
 *     notes="epistrefei success or error",
 *     type="diyexec",
 *     nickname="diy_exec",
 *     @SWG\Parameter(
 *       name="access_token",
 *       description="access_token",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="diyexec",
 *       description="p.x. datastart<br> datastop<br> for more information see list-diyexec",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="device",
 *       description="to device gia to opoio proorisete to exec",
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
 *              id="diyexec",
 *                  @SWG\Property(name="error",type="text",description="error")
 * )
 *                  @SWG\Property(name="status",type="integer",description="status code")
 *                  @SWG\Property(name="message",type="string",description="status message")
 */


//api/get/diy_getdevices.php
$app->post('/diyexec', function () use ($authenticateForRole, $diy_storage)  {
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
                $result = diy_diyexec(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});

function diy_diyexec($payload,$storage){
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    $params = loadParameters();
    $device= OAuth2\Request::createFromGlobals()->request["device"];
    $exec= OAuth2\Request::createFromGlobals()->request["exec"];
    $up=json_decode(base64_decode($payload));
    $client_id=$up->client_id;
    $diy_error["post"]["device"] = $device;
    $post["device"] = $device;                        //organisation                                  oauth_devices   
    $post["exec"] = $exec;                        //organisation                                  oauth_devices   
        $gump = new GUMP();
        $gump->validation_rules(array(
                'device'    => 'required|alpha_numeric',
                'exec'    => 'required|alpha_numeric'
        ));
        $gump->filter_rules(array(
                'device'    => 'trim|sanitize_string',
                'exec'    => 'trim|sanitize_string'
        ));
        $validated = $gump->run($post);
        if($validated === false) {
                $result["parse_errors"] = $gump->get_readable_errors(true);
                $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
        }else{
	    try {
		$stmt2 = $storage->prepare('SELECT * FROM oauth_devices WHERE device = :device');
		$stmt2->execute(array('device' => trim($device)));
		$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
		if($row2["organisation"]){
			$org = trim($row2["organisation"]);
		}
		if($row2["mode"]){
			$mode = trim($row2["mode"]);
		}
		if($row2["status"]){
			$status = trim($row2["status"]);
		}
		if($row2["client_id"]){
			$devclient_id = trim($row2["client_id"]);
		}

		$orgscopeadmin="no";
		$orgscopedevel="no";
		if($status =="org"){
                        $userscopes = explode(' ',trim($userscope));
                        $adminscope=$org."_admin";
                        $develscope=$org."_admin";
			// o user aniki sto scope
                        for ($i = 0; $i <= count($userscopes); $i++) {
                                if (trim($userscopes[$i]) == $adminscope) {
                                        $orgscopeadmin="yes";
                                }
                                if (trim($userscopes[$i]) == $develscope) {
                                        $orgscopedevel="yes";
                                }

                        }
			// einai o owner
			if($devclient_id == $client_id){
                        	$orgscopeadmin="yes";
			}
		}
		// einmai o owner
		if($status =="private" && $devclient_id == $client_id){
                	$orgscopeadmin="yes";
		}

		if($orgscopeadmin == "yes" || $orgscopedevel =="yes"){
			try {
				$stmt2 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :device');
				$stmt2->execute(array('device' => trim($device)));
				$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				if($row2["apiport"]){
					$stmt3 = $storage->prepare('SELECT * FROM oauth_diyexec WHERE exec = :exec');
					$stmt3->execute(array('exec' => trim($exec)));
					$row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
					if($row3["exec"]){

						$apiport = trim($row2["apiport"]);
						$diyexec = trim($row3["diyexec"]);
						$diyexecurl = base64_encode($diyexec);
						 $data1 = 'exec='.$diyexecurl;
						$result["result1"]=  $diyexec;

						 $ch = curl_init();
						 curl_setopt ($ch, CURLOPT_URL,"http://127.0.0.1:$apiport/api/diyexec");
						 curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
						 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
						 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
						 curl_setopt ($ch, CURLOPT_POSTFIELDS, $data1);
						 curl_setopt ($ch, CURLOPT_POST, 1);
						$r = curl_exec($ch);
						$result["DEV"]=  $r;
					}

				}
			} catch (Exception $e) {
				$diy_error["db"] = $e->getCode();
				$result["status"] = $e->getCode();
				$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
			}
		}
	    } catch (Exception $e) {
		$diy_error["db"] = $e->getCode();
		$result["status"] = $e->getCode();
		$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
	    }
	}
        if(diyConfig::read('debug') == 1){
                $result["debug"]=$diy_error;
        }

    return $result;
    
}

?>
