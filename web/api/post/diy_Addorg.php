<?php
header("Content-Type: text/html; charset=utf-8");
/**
*
* @SWG\Resource(
*   apiVersion="0.1",
*   swaggerVersion="2.0",
*   basePath="https://arduino.os.cs.teiath.gr/api",
*   resourcePath="/addorg",
*   description="Add organisation",
*   produces="['application/json']"
* )
*/
/**
 * @SWG\Api(
 *   path="/addorg",
 *   @SWG\Operation(
 *     method="POST",
 *     summary="Add organisation",
 *     notes="Create organisation kai epistrefei tis schetikes plirofories (mono se ena organisation mporeis na valeis devices)",
 *     type="addorg",
 *     nickname="add_org",
 *     @SWG\Parameter(
 *       name="access_token",
 *       description="access_token",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="org",
 *       description="organisation",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="org_desc",
 *       description="org description",
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
 *              id="addorg",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="org",type="string",description="organisation gia na valei o christis devices"),
 *                  @SWG\Property(name="org_desc",type="string",description="org desc"),
 * )
 */
//api/get/diy_Adddevice.php
// post device for add 
// access_token device org
$app->post('/addorg', function () use ($authenticateForRole, $diy_storage)  {
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
                $result = diy_addorg(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
        	//$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_addorg($payload,$storage){
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
    $org = OAuth2\Request::createFromGlobals()->request["org"];
    $org_desc = OAuth2\Request::createFromGlobals()->request["org_desc"];
    $diy_error["post"]["org"] = $org;
    $diy_error["post"]["org_desc"] = $org_desc;
    $post["org"] = $org;			//organisation					oauth_devices	
    $post["org_desc"] = $org_desc;	//mia perigrafi oti thelei o christis		oauth_devices

	$gump = new GUMP();
	$gump->validation_rules(array(
		'org'    => 'required|alpha_numeric',
		'org_desc'    => 'required|max_len,100'
	));
	$gump->filter_rules(array(
		'org'    => 'trim|sanitize_string',
		'org_desc'    => 'trim|sanitize_string'
	));
	$validated = $gump->run($post);
	if($validated === false) {
		$result["parse_errors"] = $gump->get_readable_errors(true);
		$result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
	}else{
		//check if device name exists
		$stmt = $storage->prepare('SELECT * FROM oauth_organisations WHERE organisation = :org');
		$stmt->execute(array('org' => trim($org)));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if($row){
			$result["result"]["error"] =  ExceptionMessages::OrgExist." , ". ExceptionCodes::OrgExist;
		}else{

		    try {

			// oauth_organisation table
			$stmt2 = $storage->prepare('INSERT INTO oauth_organisations (organisation, client_id, desc) VALUES (:org, :client_id, :desc)');
			$stmt2->execute(array('client_id' => $client_id, 'org' => $org, 'desc' => $org_desc));

			// scopes gia devices
			$scope=$org; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			$scope=$org."_dev"; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			$scope=$org."_dpri"; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			$scope=$org."_org"; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			$scope=$org."_dpub"; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			// scopes gia users
			$scope=$org."_view"; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			$scope=$org."_devel"; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			$scope=$org."_admin"; 
			$is_default=0;
			$stmt3 = $storage->prepare('INSERT INTO oauth_scopes (scope, is_default) VALUES (:scope, :is_default)');
			$stmt3->execute(array('scope' => $scope, 'is_default' => $is_default));

			$stmt6 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id');
			$stmt6->execute(array('client_id' => trim($client_id)));
			$row6 = $stmt6->fetch(PDO::FETCH_ASSOC);
			if($row6){
				$scope6 = $row6["scope"];
				$scope6 .=" ".$org."_admin"; 
				$scope6 .=" ".$org."_view"; 
				$stmt5 = $storage->prepare('UPDATE oauth_clients  set scope = :scope6 where client_id = :client_id');
				$stmt5->execute(array('scope6' => $scope6, 'client_id' => $client_id));
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

