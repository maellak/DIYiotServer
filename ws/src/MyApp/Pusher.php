<?php
namespace MyApp;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use MyApp\Core;
use MyApp\Config;


class Pusher implements WampServerInterface {

   /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();


    public function onSubscribe(ConnectionInterface $conn, $topic) {
	//Config::read('wss.username');
	$querystring = $topic->getId();
	$session = $conn->WAMP->sessionId;
	$wss_user = $conn->resourceId;
	$username = Config::read('wss.username');
	$password = Config::read('wss.password');
	$host = Config::read('api.host');
	 $data="grant_type=client_credentials&client_id=".$username."&client_secret=".$password;
	 $ch = curl_init();
	 curl_setopt ($ch, CURLOPT_URL,"$host/api/token");
	 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	 curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	 curl_setopt ($ch, CURLOPT_USERPWD, "$username:$password");
	 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
	 curl_setopt ($ch, CURLOPT_POST, 1);
	 $curlResponse = curl_exec ($ch);
	 curl_close($ch);
	 $curlResponse = json_decode($curlResponse, TRUE);
	 var_dump($curlResponse);

	$data = "access_token=".trim($curlResponse["access_token"]);
	$data .= "&session=". trim($conn->WAMP->sessionId);
	$data .= "&wss_user=". trim($conn->resourceId); 
	$data .= "&device=". trim($topic->getId()); 
//	echo $data; 
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL,"$host/api/wssdeviceAccess?".$data);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	$i = json_decode($result, TRUE);
echo "\n---111----\n";
var_dump($i);
echo "\n\n\n-----------------------------\n\n\n";
var_dump($result);
echo "\n---1111--".$i["result"]["view"]."--\n";
//$view=1;
	if($i["result"]["view"] == 1){
	//if($view == 1){



		if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
			$this->subscribedTopics[$topic->getId()] = $topic;
			if(array_key_exists($topic->getId(),$this->subscribedTopics)){
				echo $topic->getId()."  added with  session_id ".$conn->WAMP->sessionId." and resource_id ".$conn->resourceId."\n";
			}else{
				echo $topic->getId()." device was not added \n";
			}
		}
	}else{
				$conn->close();
	}
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
		$entryData = json_decode($entry, true);
		if (!array_key_exists($entryData['catecory'], $this->subscribedTopics)) {
		    //return;
		}

		if (array_key_exists($entryData['catecory'], $this->subscribedTopics)) {
			$topic = $this->subscribedTopics[$entryData['catecory']];

			// re-send the data to all the clients subscribed to that category
			$topic->broadcast($entryData);
		}
    }

    /* The rest of our methods were as they were, omitted from docs to save space */


    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
		//$querystring = $conn->WebSocket->request->getQuery();
		echo $querystring = $conn->WebSocket->request->getQuery();
		parse_str($querystring, $data_query);
		$data = "access_token=".$data_query["access_token"];
		$host="https://verifytoken";
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL,"$host/api/verifyToken?".$data);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$i = json_decode($result, TRUE);
		//var_dump($i);

		$authok = $i["result"]["verify"];
		$client_id = $i["result"]["client_id"];
		if ($authok == 1) {
			//$e = json_encode('Hello');
			$data = "access_token=".$data_query["access_token"];
			$data .= "&session=". $conn->WAMP->sessionId;
			$data .= "&wss_user=". $conn->resourceId; 

			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL,"$host/api/wssaddsession");
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt ($ch, CURLOPT_POST, 1);

			$result1 = curl_exec($ch);
			//var_dump($result1);
			curl_close($ch);
			$ii = json_decode($result1, TRUE);
			$sessionserver = $ii["result"]["session"];
			//var_dump($ii);
			if ($sessionserver == $conn->WAMP->sessionId ) {
				//var_dump($ii);
			}else{
				$conn->close();
			}
		}else{
			//echo 'Unable to verify access token: '."\n";
				$conn->close();
		}
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        //$topic->broadcast($event);
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
