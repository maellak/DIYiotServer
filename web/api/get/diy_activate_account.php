<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/diy_register.php
// register a user
$app->get('/activate/:code', function ($code) {
        global $app;
        $result = diy_activate_account($code);
        PrepareResponse();
        $app->response()->setBody( toGreek( json_encode( $result ) ) );
});


function diy_activate_account($code){
    global $app, $diy_storage;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    try {
        // Update client
        $storage = $diy_storage();
        $updateStmt = $storage->prepare('UPDATE oauth_users SET email_verified = 1 WHERE email_ver_code = :code');
        $updateStmt->execute(array('code' => $code));
        $result = 'Your account has been successfully activated!';
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }

    return $result;

}

