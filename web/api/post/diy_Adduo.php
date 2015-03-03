<?php

header("Content-Type: text/html; charset=utf-8");
/**
 *
 * @SWG\Resource(
 *   apiVersion="0.1",
 *   swaggerVersion="2.0",
 *   basePath="https://arduino.os.cs.teiath.gr/api",
 *   resourcePath="/adduo",
 *   description="Add user to organisation",
 *   produces="['application/json']"
 * )
 */
/**
 * @SWG\Api(
 *   path="/adduo",
 *   @SWG\Operation(
 *     method="POST",
 *     summary="Add user to organisation",
 *     notes="Add user to organisation",
 *     type="adduo",
 *     nickname="add_u_o",
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
 *              id="adduo",
 *                  @SWG\Property(name="error",type="text",description="error"),
 *                  @SWG\Property(name="status",type="integer",description="status code"),
 *                  @SWG\Property(name="message",type="string",description="status message"),
 *                  @SWG\Property(name="org",type="string",description="organisation poy tha mpei o xristis"),
 *                  @SWG\Property(name="username",type="string",description="o xristis"),
 * )
 */
//api/get/diy_Adduo.php
// post user to add to a organisation
// access_token org username
$app->post('/adduo', function () use ($authenticateForRole, $diy_storage) {
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
        $result = diy_adduo(
                $params["payload"], $params["storage"], $params["test"]
        );
        PrepareResponse();
        //$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
        $app->response()->setBody(toGreek(json_encode($result)));
    }
});

function diy_adduo($payload, $storage) {
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

    $diy_error["post"]["org"] = $org;
    $diy_error["post"]["username"] = $username;

    $post["org"] = $org;
    $post["username"] = $username;

    $gump = new GUMP();
    $gump->validation_rules(array(
        'org' => 'required|alpha_numeric',
        'username' => 'required|alpha_numeric'
    ));
    $gump->filter_rules(array(
        'org' => 'trim|sanitize_string',
        'username' => 'trim|required|alpha_numeric'
    ));
    $validated = $gump->run($post);
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
                    $scope6 = $row6["scope"];
                    $scope6 .=" " . $org . "_devel"; //den kserw an prepei na mpei kai devel i mono view!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                    $scope6 .=" " . $org . "_view";
                    $stmt5 = $storage->prepare('UPDATE oauth_clients  set scope = :scope6 where client_id = :client_id');
                    $stmt5->execute(array('scope6' => $scope6, 'client_id' => $username));
                }

                //result_messages===============================================================      
                $result["result"]["result"] = $post;
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
