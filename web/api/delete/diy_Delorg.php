<?php

header("Content-Type: text/html; charset=utf-8");
/**
 *
 * @SWG\Resource(
 *   apiVersion="0.1",
 *   swaggerVersion="2.0",
 *   basePath="https://arduino.os.cs.teiath.gr/api",
 *   resourcePath="/delorg",
 *   description="Delete organisation",
 *   produces="['application/json']"
 * )
 */
/**
 * @SWG\Api(
 *   path="/delorg",
 *   @SWG\Operation(
 *     method="DELETE",
 *     summary="Delete organisation",
 *     notes="Delete organisation kai epistrefei tis schetikes plirofories (diagrafontai kai ola ta device tou)",
 *     type="delorg",
 *     nickname="del_org",
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
 *     @SWG\ResponseMessage(code=200, message="Επιτυχία", responseModel="Success"),
 *     @SWG\ResponseMessage(code=500, message="Αποτυχία", responseModel="Failure")
 *   )
 * )
 *
 */
/**
 *
 * @SWG\Model(
 *              id="delorg",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="org",type="string",description="organisation gia na valei o christis devices"),
 * )
 */
//api/get/diy_Delorg.php
// delete org for delete 
// access_token org
$app->delete('/delorg', function () use ($authenticateForRole, $diy_storage) {
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
        $result = diy_delorg(
                $params["payload"], $params["storage"], $params["test"]
        );
        PrepareResponse();
        //$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
        $app->response()->setBody(toGreek(json_encode($result)));
    }
});

//bgazw pantou to org_desc

function diy_delorg($payload, $storage) {
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(), 1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(), 1);
    $result->method = $app->request()->getMethod();
    $up = json_decode(base64_decode($payload));
    $client_id = $up->client_id;
    $org = OAuth2\Request::createFromGlobals()->request["org"];

    $diy_error["delete"]["org"] = $org;

    $delete["org"] = $org;

    $gump = new GUMP();
    $gump->validation_rules(array(
        'org' => 'required|alpha_numeric',
    ));
    $gump->filter_rules(array(
        'org' => 'trim|sanitize_string',
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
        if (!$row) {            //false!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $result["result"]["error"] = ExceptionMessages::OrgExist . " , " . ExceptionCodes::OrgExist;
        } else {

            try {

                //1. delete to organisation
                // DELETE organisation
                $stmt1 = $storage->prepare('DELETE FROM oauth_organisations WHERE org:=org');
                $stmt1->execute(array('org' => $org));


                //2. delete ola ta scopes einai 8 !!!!!!!!!!!!!!!!!!!!!!!!!!!!! den kserw an prepei na elegxontai ptwra oi xristes kai meta na ektelite to 2.
                // DELETE scope $org
                $stmt2 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt2->execute(array('scope' => $org));

                // DELETE scope $org."_dev"
                $stmt3 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt3->execute(array('scope' => $org . "_dev"));

                // DELETE scope $org."_dpri"
                $stmt4 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt4->execute(array('scope' => $org));

                // DELETE scope $org."_org"
                $stmt5 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt5->execute(array('scope' => $org));

                // DELETE scope $org."_dpub"
                $stmt6 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt6->execute(array('scope' => $org));

                // DELETE scope $org."_view"
                $stmt7 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt7->execute(array('scope' => $org));

                // DELETE scope $org."_devel"
                $stmt8 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt8->execute(array('scope' => $org));

                // DELETE scope $org."_admin"
                $stmt9 = $storage->prepare('DELETE FROM oauth_scopes WHERE scope:=scope ');
                $stmt9->execute(array('scope' => $org));



                //3. delete kathe scope toy client pou itan melos sto organisation auto
                //3.1. ta devices exoun scope san <$org, $org."_dev", $org."_dpri", $org."_org", $org."_dpub"> (mporei ena mporei kai ola) opote prepei na kanoume ta katalilla deletes gia kathe device
                //3.2. oi xristes exoun scope san <$org."_view" $org."_devel" $org."_admin"> (mporei ena mporei kai ola) oi xristes mporei na min se kanena org opote exoume mono na diagrapsoume ena kommati apo to scope tous
                //tha prepei na ginei select gia na paroume to scope (easy)
                //kai na bgaloume apo OLO to scope pou 8a exei enas xristis mono tou organismou autou
                //kai na kanoume update
                //SELECT scope
                $stmt1 = $storage->query('SELECT * FROM oauth_clients');
                $stmt1->execute();
                while ($rowid = $stmt1->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                    $type = "none";

                    $scope = $rowid["scope"];
                    $user = $rowid["client_id"];

                    $words = explode(" ", $scope);

                    foreach ($words as $value) {
                        //an den einai tpt apo ta parakatw
                        if (strcmp($value, $org) != 0 && strcmp($value, $org . "_dev") != 0 && strcmp($value, $org . "_dpri") != 0 && strcmp($value, $org . "_org") != 0 && strcmp($value, $org . "_dpub") != 0 && strcmp($value, $org . "_view") != 0 && strcmp($value, $org . "_devel") != 0 && strcmp($value, $org . "_admin") != 0) {
                            $newscope .= ' '.$value;
                            //an einai device
                        } elseif (strcmp($value, $org) == 0 || strcmp($value, $org . "_dev") == 0 || strcmp($value, $org . "_dpri") == 0 || strcmp($value, $org . "_org") == 0 || strcmp($value, $org . "_dpub") == 0) {
                            $type = "device";
                            //an einai user
                        } elseif (strcmp($value, $org . "_view") == 0 || strcmp($value, $org . "_devel") == 0 || strcmp($value, $org . "_admin") == 0) {
                            $type = "user";
                        }
                    }

                    if ($type == "user") {
                        //UPDATE scope
                        $stmt5 = $storage->prepare('UPDATE oauth_clients  set scope = :newscope where client_id = :client_id');
                        $stmt5->execute(array('newscope' => $newscope, 'client_id' => $user));
                    } elseif ($type == "device") {
                        // DELETE all public keys for this device
                        $stmt1 = $storage->prepare('DELETE FROM oauth_public_keys WHERE client_id:=client_id');
                        $stmt1->execute(array('client_id' => $user));


                        // DELETE all port for this device
                        $stmt2 = $storage->prepare('DELETE FROM oauth_ports WHERE client_id:=client_id ');
                        $stmt2->execute(array('client_id' => $user));

                        // DELETE the device
                        $stmt3 = $storage->prepare('DELETE FROM oauth_devices WHERE device = :device');
                        $stmt3->execute(array('device' => $user));


                        //SELECT for user_id
                        $stmt4 = $storage->query('SELECT user_id FROM oauth_clients WHERE client_id=:client_id');
                        $stmt4->execute(array('client_id' => $user));
                        $rowid = $stmt4->fetch(PDO::FETCH_ASSOC);
                        if ($rowid) {
                            $user_id = $rowid["user_id"];
                        }

                        //DELETE the user for the device
                        $stmt5 = $storage->prepare('DELETE FROM oauth_users WHERE user_id:=user_id');
                        $stmt5->execute(array('user_id' => $user_id));


                        //DELETE the the connect with the client and device
                        $stmt6 = $storage->prepare('DELETE FROM oauth_clients WHERE client_id=:client_id');
                        $stmt6->execute(array('client_id' => $user));
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
