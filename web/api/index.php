<?php
header("Content-Type: text/html; charset=utf-8");
header('Content-Type: application/json');

chdir("../server");

require_once('system/includes.php');
require_once('libs/Slim/Slim.php');

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->config('debug', true);


//===========================================================
$authenticateForRole = function () 
{
	global $conOptions;
	$_dsn = $conOptions->dsn;
	$_username = $conOptions->username;
	$_password = $conOptions->password;
	$storage = new OAuth2\Storage\Pdo(array('dsn' => $_dsn, 'username' => $_username, 'password' => $_password));
	$server = new OAuth2\Server($storage);
	$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage), array(
			'allow_credentials_in_request_body => true'
		)); 

	$cryptoStorage = new OAuth2\Storage\CryptoToken($storage);
	$server->addStorage($cryptoStorage, "access_token");
		
	$cryptoResponseType = new OAuth2\ResponseType\CryptoToken($storage);
	$server->addResponseType($cryptoResponseType);
	return $server;

};

$diy_storage = function () 
{
	global $conOptions;
	$_dbfile = $conOptions->dbfile;
 	$db = new PDO(sprintf('sqlite:%s', $_dbfile));
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	return $db;
};

//=========================  POST ==================================

$app->post('/token', function () use ($authenticateForRole)  {
	$server = $authenticateForRole();
	$rr =  $server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
	return $rr;
});

//=========================  GET ==================================

//api/get/diy_getdevices.php
$app->get('/devices', function () use ($authenticateForRole, $diy_storage)  {
    	global $app;
    	$params = loadParameters();
	$server = $authenticateForRole();
	$dbstorage = $diy_storage();
	if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
		echo 'Unable to verify access token: '."\n";
		$server->getResponse()->send();
		die;
	}else{
		$crypto_token = OAuth2\Request::createFromGlobals()->query["access_token"];
		$separator = '.';
		list($header, $payload, $signature) = explode($separator, $crypto_token);
		//echo base64_decode($payload);
		$params["payload"] = $payload;
		$params["storage"] = $dbstorage;
		$result = diy_getdevices(
			$params["payload"],
			$params["storage"],
			$params["test"]
           	);      
    		PrepareResponse();
    		$app->response()->setBody( toGreek( json_encode( $result ) ) );
	}
});

//=========================  HELPER ==================================
//function not found
$app->notFound(function () use ($app) 
{
    $controller = $app->environment();
    $controller = substr($controller["PATH_INFO"], 1);

    try
    {
       if ( !in_array( strtoupper($app->request()->getMethod()), array(MethodTypes::GET, MethodTypes::POST, MethodTypes::PUT, MethodTypes::DELETE)))
            throw new Exception(ExceptionMessages::MethodNotFound, ExceptionCodes::MethodNotFound);
        else
            throw new Exception(ExceptionMessages::MethodNotFound, ExceptionCodes::MethodNotFound);
    } 
    catch (Exception $e) 
    {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$app->request()->getMethod()."][".$controller."]:".$e->getMessage();
    }

    echo toGreek( json_encode( $result ) ); 

});

$app->run();

//=========================================================================

function PrepareResponse()
{
    global $app;

    $app->contentType('application/json');
    $app->response()->headers()->set('Content-Type', 'application/json; charset=utf-8');
    $app->response()->headers()->set('X-Powered-By', 'DIYiot Tools');
    $app->response()->setStatus(200);
}


function UrlParamstoArray($params)
{
    $items = array();
    foreach (explode('&', $params) as $chunk) {
        $param = explode("=", $chunk);
        $items = array_merge($items, array($param[0] => urldecode($param[1])));
    }
    return $items;

}

function loadParameters()
{
    global $app;

    if ($app->request->getBody())
    {
        if ( is_array( $app->request->getBody() ) )
            $params = $app->request->getBody();
        else if ( json_decode( $app->request->getBody() ) )
            $params = get_object_vars( json_decode($app->request->getBody(), false) );
        else
            $params = UrlParamstoArray($app->request->getBody());
    }
    else
    {
        if ( json_decode( key($_REQUEST) ) )
            $params = get_object_vars( json_decode(key($_REQUEST), false) );
        else
            $params = $_REQUEST;
    }
    
    // array to object
    //$params = json_decode (json_encode ($params), FALSE);

    return $params;
}

function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

function toGreek($value)
{
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $value ? $value : array());
}

?>
