<?php

header("Content-Type: text/html; charset=utf-8");
/**
 *
 * @SWG\Resource(
 *   apiVersion="0.1",
 *   swaggerVersion="2.0",
 *   basePath="https://arduino.os.cs.teiath.gr/api",
 *   resourcePath="/deldevice",
 *   description="Delete device",
 *   produces="['application/json']"
 * )
 */
/**
 * @SWG\Api(
 *   path="/deldevice",
 *   @SWG\Operation(
 *     method="DELETE",
 *     summary="Delete device from a organisation",
 *     notes="Delete device from a organisation kai epistrefei tis schetikes plirofories. <br>To Organisation prepei na yparchei kai o christis na einai o owner i na aniki sto Organisations admin scope",
 *     type="deldevice",
 *     nickname="del_device",
 *     @SWG\Parameter(
 *       name="access_token",
 *       description="access_token",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="org",
 *       description="organisation gia to device",
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
 *              id="deldevice",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="org",type="string",description="organisation pou aniki to device"),
 *                  @SWG\Property(name="device",type="string",description="device name"),
 *                  @SWG\Property(name="status",type="string",description="status of device private/org/public"),
 *                  @SWG\Property(name="mode",type="string",description="mode of device devel/production")
 * )
 */
//api/delete/diy_Deldevice.php
// delete device for delete 
// access_token device org
$app->delete('/deldevice', function () use ($authenticateForRole, $diy_storage) {
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
        $result = diy_deldevice(
                $params["payload"], $params["storage"], $params["test"]
        );
        PrepareResponse();
        //$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
        $app->response()->setBody(toGreek(json_encode($result)));
    }
});

function diy_deldevice($payload, $storage) {
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
    $userscope = $up->scope;
    $org = OAuth2\Request::createFromGlobals()->request["org"];
    $device = OAuth2\Request::createFromGlobals()->request["device"];

    $diy_error["delete"]["org"] = $org;
    $diy_error["delete"]["device"] = $device;

    $delete["org"] = $org;   //organisation					oauth_devices	
    $delete["device"] = $device;    // to client_id tou device			oauth_devices	oauth_clients	oauth_public_keys

    $gump = new GUMP();
    $gump->validation_rules(array(
        'org' => 'required|alpha_numeric',
        'device' => 'required|alpha_numeric',
        'client_secret' => 'required|max_len,100|min_len,6',
        'device_desc' => 'required|max_len,100'
    ));
    $gump->filter_rules(array(
        'org' => 'trim|sanitize_string',
        'device' => 'trim|sanitize_string',
        'client_secret' => 'trim',
        'device_desc' => 'trim|sanitize_string'
    ));
    $validated = $gump->run($delete);
    if ($validated === false) {
        $result["parse_errors"] = $gump->get_readable_errors(true);
        $result["message"] = "[" . $result["method"] . "][" . $result["function"] . "]:" . $gump->get_readable_errors(true);
    } else {

        //check if org name exists
        $orgexists = "no";
        $stmtorg = $storage->prepare('SELECT * FROM oauth_organisations WHERE organisation = :org');
        $stmtorg->execute(array('org' => trim($org)));
        $roworg = $stmtorg->fetch(PDO::FETCH_ASSOC);
        if ($roworg) {
            $orgexists = "yes";
            //$result["result"]["error"] =  ExceptionMessages::OrgExist." , ". ExceptionCodes::OrgExist;

            $orgadmin = "no";
            $orgowner = "no";
            $userscopes = explode(' ', trim($userscope));
            $orgscope = $org . "_admin";
            for ($i = 0; $i <= count($userscopes); $i++) {
                if (trim($userscopes[$i]) == $orgscope) {
                    $orgadmin = "yes";
                }
            }
            if ($orgadmin == "no") {
                //check if org name exists and client_id
                $stmtorg1 = $storage->prepare('SELECT * FROM oauth_organisations WHERE organisation = :org and client_id = :client_id');
                $stmtorg1->execute(array('org' => trim($org), 'client_id' => $client_id));
                $roworg1 = $stmtorg1->fetch(PDO::FETCH_ASSOC);
                if (!$roworg1) {
                    $result["result"]["error"] = ExceptionMessages::OrgOwner . " , " . ExceptionCodes::OrgOwner;
                } else {
                    $orgowner = "yes";
                }
            }
        } else {
            $result["result"]["error"] = ExceptionMessages::OrgNotExist . " , " . ExceptionCodes::OrgNotExist;
        }

        //check if device name exists
        $orgdeviceexists = "no";
        $stmt = $storage->prepare('SELECT client_id  FROM oauth_clients WHERE client_id = :device');
        $stmt->execute(array('device' => trim($device)));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $orgdeviceexists = "yes";
        }



//DEL!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        if (($orgexists == "yes" && ($orgowner == "yes" || $orgadmin == "yes")) && $orgdeviceexists == "yes") {

            try {

                // DELETE all public keys for this device
                $stmt1 = $storage->prepare('DELETE FROM oauth_public_keys WHERE client_id:=client_id');
                $stmt1->execute(array('client_id' => $device));


                // DELETE all port for this device
                $stmt2 = $storage->prepare('DELETE FROM oauth_ports WHERE client_id:=client_id ');
                $stmt2->execute(array('client_id' => $device));

                // DELETE the device
                $stmt3 = $storage->prepare('DELETE FROM oauth_devices WHERE device = :device');
                $stmt3->execute(array('device' => $device));


                //SELECT for user_id
                $stmt4 = $storage->query('SELECT user_id FROM oauth_clients WHERE client_id=:client_id');
				$stmt4->execute(array('client_id' => $device));
                $rowid = $stmt4->fetch(PDO::FETCH_ASSOC);
                if ($rowid) {
                    $user_id = $rowid["user_id"];
                }

                //DELETE the user for the device
                $stmt5 = $storage->prepare('DELETE FROM oauth_users WHERE user_id:=user_id');
                $stmt5->execute(array('user_id' => $user_id));


                //DELETE the the connect with the client and device
                $stmt6 = $storage->prepare('DELETE FROM oauth_clients WHERE client_id=:client_id');
                $stmt6->execute(array('client_id' => $device));
				

                $delete["status"] = $status;
                $delete["mode"] = $mode;

                //result_messages================================================================================================================================================================================================      
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
