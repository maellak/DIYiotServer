<?php

header("Content-Type: text/html; charset=utf-8");
/**
 *
 * @SWG\Resource(
 *   apiVersion="0.1",
 *   swaggerVersion="2.0",
 *   basePath="https://arduino.os.cs.teiath.gr/api",
 *   resourcePath="/deluo",
 *   description="delete user from organisation",
 *   produces="['application/json']"
 * )
 */
/**
 * @SWG\Api(
 *   path="/deluo",
 *   @SWG\Operation(
 *     method="DELETE",
 *     summary="delete user from organisation",
 *     notes="delete user from organisation",
 *     type="deluo",
 *     nickname="del_u_o",
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
 *       name="username",
 *       description="username",
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
 *              id="deluo",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="org",type="string",description="organisation poy tha mpei o xristis"),
 *                  @SWG\Property(name="username",type="string",description="o xristis"),
 * )
 */
//api/get/diy_Deluo.php
// delete user to delete from a organisation
// access_token org username
$app->$delete('/deluo', function () use ($authenticateForRole, $diy_storage) {
    global $app;
    $params = loadParameters();
    $server = $authenticateForRole();
    $dbstorage = $diy_storage();
    if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
        $server->getResponse()->send();
        die;
    } else {
        $crypto_token = OAuth2\Request::createFromGlobals()->request["access_token"];
        $separator = '.';
        list($header, $payload, $signature) = explode($separator, $crypto_token);
        //echo base64_decode($payload);
        $params["payload"] = $payload;
        $params["storage"] = $dbstorage;
        $result = diy_deluo(
                $params["payload"], $params["storage"], $params["test"]
        );
        PrepareResponse();
        //$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
        $app->response()->setBody(toGreek(json_encode($result)));
    }
});

function diy_deluo($payload, $storage) {
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(), 1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(), 1);
    $result->method = $app->request()->getMethod();
    //$params = loadParameters();
    $up = json_decode(base64_decode($payload));
    $client_id = $up->client_id;

    $org = OAuth2\Request::createFromGlobals()->request["org"];
    $username = OAuth2\Request::createFromGlobals()->request["username"];

    $diy_error["delete"]["org"] = $org;
    $diy_error["delete"]["username"] = $username;

    $delete["org"] = $org;
    $delete["username"] = $username;

    $gump = new GUMP();
    $gump->validation_rules(array(
        'org' => 'required|alpha_numeric',
        'username' => 'required|alpha_numeric'
    ));
    $gump->filter_rules(array(
        'org' => 'trim|sanitize_string',
        'username' => 'trim|required|alpha_numeric'
    ));
    $validated = $gump->run($delete);
    if ($validated === false) {
        $result["parse_errors"] = $gump->get_readable_errors(true);
        $result["message"] = "[" . $result["method"] . "][" . $result["function"] . "]:" . $gump->get_readable_errors(true);
    } else {

        //check if organisation name exists
        $stmt = $storage->prepare('SELECT * FROM oauth_organisations WHERE organisation = :org');
        $stmt->execute(array('org' => trim($org)));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $result["result"]["error"] = ExceptionMessages::OrgExist . " , " . ExceptionCodes::OrgExist;
        } else {

            try {
                //check if user exist
                $stmt6 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id');
                $stmt6->execute(array('client_id' => trim($username)));
                $row6 = $stmt6->fetch(PDO::FETCH_ASSOC);
                if ($row6) {
					
					//SELECT scope
					$stmt1 = $storage->query('SELECT scope FROM oauth_clients WHERE client_id=:client_id');
					$stmt1->execute(array('client_id' => $username));
					$rowid = $stmt4->fetch(PDO::FETCH_ASSOC);
					if ($rowid) {
						$scope = $rowid["scope"];
						
						// xwrizoume to string me ta kena
						$words = explode(" ", $scope);
						
						foreach($words as $value) {
							if( strcmp($value,$org . "_devel")!=0 && strcmp($value,$org . "_view")!=0 ){
								$newscope .= ' '.$value;
							}
						}
						
						//UPDATE scope
						$stmt5 = $storage->prepare('UPDATE oauth_clients  set scope = :newscope where client_id = :client_id');
						$stmt5->execute(array('newscope' => $newscope, 'client_id' => $username));
					}
                }

                //result_messages===============================================================      
                $result["result"]["result"] = $delete;
                $result["result"]["session"] = $session;
                $result["error"] = $error;
                $result["status"] = "200";
                $result["message"] = "[" . $result["method"] . "][" . $result["function"] . "]: NoErrors";
            } catch (Exception $e) {
                $result["status"] = $e->getCode();
                $result["message"] = "[" . $result["method"] . "][" . $result["function"] . "]:" . $e->getMessage();
            }
        }
    }
    if (diyConfig::read('debug') == 1) {
        $result["debug"] = $diy_error;
    }

    return $result;
}
