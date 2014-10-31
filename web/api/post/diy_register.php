<?php
header("Content-Type: text/html; charset=utf-8");
//api/get/diy_register.php
// register a user
$app->post('/register', function () {
        global $app;
        $result = diy_register();
        PrepareResponse();
        $app->response()->setBody( toGreek( json_encode( $result ) ) );
});


function diy_register(){
    global $app, $diy_storage;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $client_id = $params["client_id"];
    $client_secret = $params["client_secret"];
    $firstname = $params["first_name"];
    $lastname = $params["last_name"];
    $email = $params["email"];
    $post["client_id"] = $client_id;
    $post["client_secret"] = $client_secret;
    $post["firstname"] = $firstname;
    $post["lastname"] = $lastname;
    $post["email"] = $email;
    foreach($post as $curKey => $curValue) { $diy_error["post"][$curKey] = $curValue; }
        $gump = new GUMP();
        $gump->validation_rules(array(
                'client_id'    => 'required|alpha_numeric',
                'client_secret'    => 'required|alpha_numeric',
                'firstname'    => 'required|alpha_numeric',
                'lastname'    => 'required|alpha_numeric',
                'email'    => 'required|valid_email',
        ));
        $gump->filter_rules(array(
                'client_id'    => 'trim|sanitize_string',
                'client_secret'    => 'trim|sanitize_string',
                'firstname'    => 'trim|sanitize_string',
                'lastname'    => 'trim|sanitize_string',
                'email'    => 'trim|sanitize_string',
        ));
        $validated = $gump->run($post);
        if($validated === false) {
                $result["parse_errors"] = $gump->get_readable_errors(true);
                $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$gump->get_readable_errors(true);
    }

    try {
    if(count($result["parse_errors"]) <= 0) {
        $storage = $diy_storage();
        $lastkey = $storage->query('SELECT user_id FROM oauth_users ORDER BY user_id DESC LIMIT 1');
        foreach($lastkey as $curRow) { $lastkey = intval($curRow[0]); }
        $code = md5($post["firstname"].$post["lastname"].$post["email"]);

        // Create user
        $storage->query('INSERT INTO oauth_users (user_id, first_name, last_name, email, email_verified, email_ver_code)
        VALUES ('.($lastkey + 1).', "'.$post["firstname"].'", "'.$post["lastname"].'", "'.$post["email"].'", 0, "'.$code.'")');
        $user_id = $storage->lastInsertId();

        // Create client
        $publicKey  = file_get_contents('../../ssh/CLIENT_ID1_pubkey.pem');
        $privateKey = file_get_contents('../../ssh/CLIENT_ID1_privkey.pem');
        $storage->query('INSERT INTO oauth_clients (client_id, client_secret, scope, user_id) VALUES ("'.$post["client_id"].'", "'.$post["client_secret"].'", "main", '.$user_id.')');
        $client_id = $storage->lastInsertId();
        $storage->query('INSERT INTO oauth_public_keys (client_id, public_key, private_key, encryption_algorithm) VALUES ("'.$post["client_id"].'", "'.$publicKey.'", "'.$privateKey.'", "RS256")');

        // Send email
        $link = 'https://'.$_SERVER['HTTP_HOST'].'/api/activate/'.$code;
        $transport = Swift_SmtpTransport::newInstance('smtp.teiath.gr', 25);
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance('Wonderful Subject')
            ->setFrom(array('dnna@teiath.gr' => 'Diyiot'))
            ->setTo(array($post["email"]))
            ->setSubject('Welcome to diyiot')
            ->setBody('Hi '.$post["firstname"].',<BR /><BR />To active your account please click the following link <a href="'.$link.'">'.$link.'</a>.', 'text/html', 'UTF-8')
        ;
        $mailer->send($message);
    }
    //result_messages===============================================================      
        $result["result"]["user_id"] = $user_id;
        $result["error"]=  $error;
        $result["status"] = "200";
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    } catch (Exception $e) {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
        if(isset($user_id)) {
            $storage->query('DELETE FROM oauth_users WHERE user_id = '.$user_id);
        }
    }

    if(diyConfig::read('debug') == 1){
    $result["debug"]=$diy_error;
    }

    return $result;

}

