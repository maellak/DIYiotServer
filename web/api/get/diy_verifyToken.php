<?php
header("Content-Type: text/html; charset=utf-8");
//api/post/diy_verifyToken.php
//verifyToken ratchet
$app->get('/verifyToken', function () use ($authenticateForRole)  {
        global $app;
        $params = loadParameters();
        $server = $authenticateForRole();
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
                $result = diy_verifyToken(
            		$params["verify"] = 0
                );
                PrepareResponse();
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }else{
	 	$crypto_token = OAuth2\Request::createFromGlobals()->query["access_token"];
                $separator = '.';
                list($header, $payload, $signature) = explode($separator, $crypto_token);
		$up=json_decode(base64_decode($payload));
		$client_id=$up->client_id;

                $result = diy_verifyToken(
            		$params["verify"] = 1,
            		$params["client_id"] = $client_id
                );
                PrepareResponse();
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_verifyToken($verify, $client_id){
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    //$params = loadParameters();
    try {
	//result_messages===============================================================      
        $result["result"]["verify"]=  $verify;
        $result["result"]["client_id"]=  $client_id;
        $result["status"] = "200";
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }
    return $result;

}

