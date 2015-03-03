<?php

header("Content-Type: text/html; charset=utf-8");
/**
 *
 * @SWG\Resource(
 *   apiVersion="0.1",
 *   swaggerVersion="2.0",
 *   basePath="https://arduino.os.cs.teiath.gr/api",
 *   resourcePath="/deluser",
 *   description="Delete User",
 *   produces="['application/json']"
 * )
 */
/**
 * @SWG\Api(
 *   path="/deluser",
 *   @SWG\Operation(
 *     method="DELETE",
 *     summary="Delete user",
 *     notes="Delete user",
 *     type="deluser",
 *     nickname="del_user",
 *     @SWG\Parameter(
 *       name="client_id",
 *       description="client_id alpha_numeric",
 *       required=true,
 *       type="text",
 *       paramType="query"
 *     ),
 *     @SWG\Parameter(
 *       name="client_secret",
 *       description="client_secret min 6",
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
 *              id="deluser",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 * )
 */
//api/get/diy_Deluser.php
// delete user for delete 
// access_token user
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
        $result = diy_deluser(
                $params["payload"], $params["storage"], $params["test"]
        );
        PrepareResponse();
        //$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
        $app->response()->setBody(toGreek(json_encode($result)));
    }
});

function diy_deluser($payload, $storage) {
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

    $client_id = OAuth2\Request::createFromGlobals()->request["username"];
    $client_secret = OAuth2\Request::createFromGlobals()->request["passwrd"];

    $diy_error["delete"]["client_id"] = $client_id;
    $diy_error["delete"]["client_secret"] = $client_secret;

    $delete["client_id"] = $client_id;
    $delete["client_secret"] = $client_secret;

    $gump = new GUMP();
    $gump->validation_rules(array(
        'client_id' => 'required|alpha_numeric',
        'client_secret' => 'required|alpha_numeric',
    ));
    $gump->filter_rules(array(
        'client_id' => 'trim|sanitize_string',
        'client_secret' => 'trim|sanitize_string',
    ));
    $validated = $gump->run($post);
    if ($validated === false) {
        $result["parse_errors"] = $gump->get_readable_errors(true);
        $result["message"] = "[" . $result["method"] . "][" . $result["function"] . "]:" . $gump->get_readable_errors(true);
    } else {
        //check if username exists
        $stmt = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id AND client_secret = :client_secret');
        $stmt->execute(array('client_id' => $client_id, 'client_secret' => $client_secret));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {            //false!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $result["result"]["error"] = ExceptionMessages::OrgExist . " , " . ExceptionCodes::OrgExist;
        } else {

            try {
                //SELECT for user_id
                $stmt1 = $storage->query('SELECT user_id FROM oauth_clients WHERE client_id=:client_id');
                $stmt1->execute(array('client_id' => $client_id));
                $rowid = $stmt4->fetch(PDO::FETCH_ASSOC);
                if ($rowid) {
                    $user_id = $rowid["user_id"];
                }

                //DELETE the user for the user
                $stmt2 = $storage->prepare('DELETE FROM oauth_users WHERE user_id:=user_id');
                $stmt2->execute(array('user_id' => $user_id));


                //DELETE the the connect with the client and user
                $stmt3 = $storage->prepare('DELETE FROM oauth_clients WHERE client_id=:client_id');
                $stmt3->execute(array('client_id' => $client_id));

                //result_messages===============================================================      
                $result["result"]["user_id"] = $user_id;
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
