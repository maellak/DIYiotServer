<?php

include("myhost.php");

/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

 $data="grant_type=client_credentials&client_id=".$username."&client_secret=".$password;
echo $data."\n";
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
echo $curlResponse['access_token']."\n";
$access_token = $curlResponse['access_token'];
 $file_path = realpath("firmware.hex");


 $data2 = file_get_contents($file_path);
 $data3 = base64_encode($data2);

 $data1 = 'access_token='.$curlResponse['access_token'].'&test=test';
 $data1 .= '&device=dimdevice';
 $data1 .= '&binfile='.$data3;

 $ch = curl_init();
 curl_setopt ($ch, CURLOPT_URL,"$host/api/writedevice");
 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);                                                                                                              
 curl_setopt ($ch, CURLOPT_POSTFIELDS, $data1);
 curl_setopt ($ch, CURLOPT_POST, 1);
 
var_dump(curl_getinfo($ch));
$result = curl_exec($ch);
echo " --------------------------------------------\n\n";
var_dump($result);
echo " --------------------------------------------\n\n";
 curl_close($ch);
