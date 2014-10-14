<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/diy_devsshkeys.php
//make user and ssh keys fro ssh -i connections
// periorismos epissis tou user na mporei mono na anigi post kai tipota allo
$app->post('/devsshkeys', function () use ($authenticateForRole, $diy_storage)  {
        global $app;
        $params = loadParameters();
        $server = $authenticateForRole();
        $dbstorage = $diy_storage();
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
                echo 'Unable to verify access token: '."\n";
                $server->getResponse()->send();
                die;
        }else{
                $crypto_token = OAuth2\Request::createFromGlobals()->request["access_token"];
                $separator = '.';
                list($header, $payload, $signature) = explode($separator, $crypto_token);
                //echo base64_decode($payload);
                $params["payload"] = $payload;
                $params["storage"] = $dbstorage;
                $result = diy_devsshkeys(
                        $params["payload"],
                        $params["storage"],
                        $params["test"]
                );
                PrepareResponse();
        	//$result["result"]=  var_export(OAuth2\Request::createFromGlobals(),true);
                $app->response()->setBody( toGreek( json_encode( $result ) ) );
        }
});


function diy_devsshkeys($payload,$storage){
    global $app;
    global $conOptions;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    //$params = loadParameters();
    $up=json_decode(base64_decode($payload));
    $client_id=$up->client_id;
    try {
        $public_key = OAuth2\Request::createFromGlobals()->request["public_key"];
        $public_key = trim($public_key);

	$stmt1 = $storage->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id');
        $stmt1->execute(array('client_id' => $client_id));
	 foreach ($stmt1 as $row) {
		$sshport= $row["sshport"];
		$apiport= $row["apiport"];
		$dataport= $row["dataport"];
	 }

        $stmt = $storage->prepare('UPDATE oauth_devices set public_key=:public_key where client_id=:client_id');
        $stmt->execute(array('client_id' => $client_id, 'public_key' => $public_key));
	$pos  = mb_strripos($public_key, ' ');
	$s = 0; 
	$public = mb_substr($public_key, $s, $pos); 
	//result_messages===============================================================      
	$auth_settings = 'no-pty,no-X11-forwarding,permitopen="localhost:'.$dataport.'",permitopen="localhost:'.$apiport.'",command="/bin/echo do-not-send-commands" '.$public.' '.$client_id.'=@OpenWrt';
	//$conOptions->sshhome;	
$c = <<<EOD
  #include <stdlib.h>
  #include <sys/types.h>
  #include <unistd.h>

  int
  main (int argc, char *argv[])
  {
     setuid (0);

     /* WARNING: Only use an absolute path to the script to execute,
      *          a malicious user might fool the binary and execute
      *          arbitary commands if not.
      * */

     system ("/bin/mkdir /home/SSH/$client_id");

     return 0;
   }
EOD;

//file_put_contents('/var/www/exec/php_exec.c', $c);
//exec("/usr/bin/gcc /var/www/exec/php_exec.c -o /var/www/exec/php_exec"); 
//exec("/usr/bin/chown root php_root");
//exec("/usr/bin/chmod u=rwx,go=xr,+s /var/www/exec/php_exec"); 
//exec("/var/www/exec/php_exec"); 
file_put_contents('../tmp/authorized_keys', $auth_settings);
//	mkdir("$conOptions->sshhome/$client_id"); 
        //$result["result"]=  $app->request()->get("public_key");
        $result["result"]=  $auth_settings.$public_key;
        $result["error"]=  $error;
        $result["status"] = "200";
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
    }
    return $result;

}

