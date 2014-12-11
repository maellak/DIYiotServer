<?php

include("myhost.php");

/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
 $data = 'access_token='.$curlResponse['access_token'].'&test=test';
 $data .= '&device=device1234567';
 $data .= '&orgto=testorg1234567';
 var_dump($curlResponse);
 $ch = curl_init();
 curl_setopt ($ch, CURLOPT_URL,"$host/api/movedevice?".$data);
 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);                                                                                                              
 
$result = curl_exec($ch);
 curl_close($ch);
 echo "\n\nclient: ".$result."\n\n";
$i = json_decode($result, TRUE);
//echo  $i->result->dev->device;
var_dump($i);
echo "\n\ndev: ";
echo  $i["result"]["dev"][0]["device"];
echo " \n\n";
