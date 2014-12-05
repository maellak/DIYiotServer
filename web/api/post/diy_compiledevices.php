<?php

header("Content-Type: text/html; charset=utf-8");
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

/**
*
* @SWG\Resource(
*   apiVersion="0.1",
*   swaggerVersion="2.0",
*   basePath="https://arduino.os.cs.teiath.gr/api",
*   resourcePath="/compile",
*   description="Compile Write sketch to device",
*   produces="['application/json']"
* )
*/

/**
 * @SWG\Api(
 *   path="/compile",
 *   @SWG\Operation(
 *     method="POST",
 *     summary="Compile and Write sketch to device",
 *     notes="epistrefei success or error",
 *     type="compile",
 *     nickname="compile_device",
 *     @SWG\Parameter(
 *       name="access_token",
 *       description="access_token",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="srcfile",
 *       description="src file base64_encode",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="srclib",
 *       description="array with libs. base64_encode",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="filename",
 *       description="filename",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="comp",
 *       description="compiler    avrgcc, ino",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="writedevice",
 *       description="yes/no",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="device",
 *       description="to device gia to opoio proorisete to file",
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
 *              id="writedevice",
 *                  @SWG\Property(name="error",type="text",description="error")
 * )
 *                  @SWG\Property(name="status",type="integer",description="status code")
 *                  @SWG\Property(name="message",type="string",description="status message")
 */


//api/get/diy_getdevices.php
$app->post('/compile', function () use ($authenticateForRole, $diy_storage)  {
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
                $result = diy_compile(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});

function diy_compile($payload,$storage){
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    $params = loadParameters();
    $srcfile= OAuth2\Request::createFromGlobals()->request["srcfile"];
    $srclib= OAuth2\Request::createFromGlobals()->request["srclib"];
    $device= OAuth2\Request::createFromGlobals()->request["device"];
    $comp= OAuth2\Request::createFromGlobals()->request["comp"];
    $filename= OAuth2\Request::createFromGlobals()->request["filename"];
    $writedevice= OAuth2\Request::createFromGlobals()->request["writedevice"];
    $up=json_decode(base64_decode($payload));
    $client_id=$up->client_id;
    $diy_error["post"]["device"] = $device;
    $post["srcfile"] = $srcfile;                        //organisation                                  oauth_devices   
    $post["device"] = $device;                        //organisation                                  oauth_devices   
    $post["comp"] = $comp;                        //organisation                                  oauth_devices   
    $post["filename"] = $filename;                        //organisation                                  oauth_devices   
    $post["writedevice"] = $writedevice;                        //organisation                                  oauth_devices   
        $gump = new GUMP();
        $gump->validation_rules(array(
                'device'    => 'required|alpha_numeric',
                'filename'    => 'required|alpha_numeric',
                'comp'    => 'required|alpha_numeric',
                'writedevice'    => 'required|alpha_numeric'
        ));
        $gump->filter_rules(array(
                'device'    => 'trim|sanitize_string',
                'filename'    => 'trim|sanitize_string',
                'comp'    => 'trim|sanitize_string',
                'writedevice'    => 'trim|sanitize_string'
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
		if($mode == "devel" && $status =="org"){
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
		if($mode == "devel" && $status =="private" && $devclient_id == $client_id){
                	$orgscopeadmin="yes";
		}

				$result["result"]["sketch1"]=  $orgscopeadmin;
		if($orgscopeadmin == "yes" || $orgscopedevel =="yes"){
			try {
				$stmt2 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :device');
				$stmt2->execute(array('device' => trim($device)));
				$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				if($row2["apiport"]){

					// *************************************** compiler *********************************
					// srcfile echeis se base64 ton kodika
					// compiler echeis ton compiler pou thelei o user   mechri stigmis echoume   gcc, ino
					// filename to filename pou edosse o user

					// o poros compilesketch 
					// afou kanei compile
					// epistrefei 
					// error   ta lathi  h noerrors
					// binfile    to hex file
					// sou alaxa 
                    			//$srcfilebase64encode = urlencode(base64_encode(urlencode($srcfile)));
					// se
                    			$srcfilebase64encode = $srcfile;
					// to stelno iodi se base64_encode
					// Dimo
					// edo echeis kai tin metavliti 
					// $srclib pou legame
					// einai se base64_encode
					$compilerserver =  diyConfig::read("compiler.host");
					$compilerserver .=  ":".diyConfig::read("compiler.port");
					 $data1 = 'filename='.$filename;
					 $data1 .= '&compiler='.$comp;
					 $data1 .= '&srcfile='.$srcfilebase64encode;


					 $ch = curl_init();
					 curl_setopt ($ch, CURLOPT_URL,"$compilerserver/api/compilesketch");
					 curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
					 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
					 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
					 curl_setopt ($ch, CURLOPT_POSTFIELDS, $data1);
					 curl_setopt ($ch, CURLOPT_POST, 1);
					$r = curl_exec($ch);
					$result["compiler"]=  $r;
					$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
					$result["status"] = "200";
                    
                    $r = json_decode($r, true);
                    if(!$r) { echo 'Error: '.$r; die(); }
                    if($r['status'] != 200) {
                        $result["message"] = "[".$result["method"]."][".$result["function"]."]: CompilationError";
                        $result["status"] = "500";
                        return $result;
                    }
					
                    //$srcfilebase64encode = base64_encode($srcfile);
					$apiport = trim($row2["apiport"]);


					// *************************************** compiler *********************************

					if($r['status'] == 200 && $writedevice == "yes"){
						$apiport = trim($row2["apiport"]);
                        $binfile = $r['hex'];
						$data1 = 'file=base64';
						$data1 .= '&binfile='.$binfile;

						 $ch = curl_init();
						 curl_setopt ($ch, CURLOPT_URL,"http://127.0.0.1:$apiport/api/writesketch");
						 curl_setopt ($ch, CURLOPT_TIMEOUT, 90);
						 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
						 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
						 curl_setopt ($ch, CURLOPT_POSTFIELDS, $data1);
						 curl_setopt ($ch, CURLOPT_POST, 1);
						$r = curl_exec($ch);
						$result["sketch"]=  $r;
						$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
						$result["status"] = "200";
						//$result["result"]=  $r;
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
