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
	//global $conOptions;
	$_dsn = diyConfig::read('db.dsn');
	$_username = diyConfig::read('db.username');
	$_password = diyConfig::read('db.password');
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
	//global $conOptions;
	$_dbfile = diyConfig::read('db.file');
 	$db = new PDO(sprintf('sqlite:%s', $_dbfile));
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	return $db;
};

$diy_exception = function () 
{
 	$exception = new CustomException();
	return $exception;
};

//=========================  POST ==================================

$app->post('/token', function () use ($authenticateForRole)  {
	$server = $authenticateForRole();
	$rr =  $server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
	return $rr;
});




/*Directories that contain api POST/GET*/
$diy_classesDir = array (
    '../api/post/',
    '../api/get/',
    '../api/put/',
    '../api/delete/'
);
foreach ($diy_classesDir as $directory) {
        foreach (glob("$directory*.php") as $__filename){
            require_once ($__filename);
        }
}


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

function diy_validate64($buffer)
{
  $VALID  = 1;
  $INVALID= 0;

  $p    = $buffer;
  $len  = strlen($p);

  for($i=0; $i<$len; $i++)
  {
     if( ($p[$i]>="A" && $p[$i]<="Z")||
         ($p[$i]>="a" && $p[$i]<="z")||
         ($p[$i]>="/" && $p[$i]<="9")||
         ($p[$i]=="+")||
         ($p[$i]=="=")||
         ($p[$i]=="\x0a")||
         ($p[$i]=="\x0d")
       )
       continue;
     else
       return $INVALID;
  }  //fall through if all ok
return $VALID;
};

?>
