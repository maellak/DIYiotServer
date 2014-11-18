<?php


$host="127.0.0.1:50001";
/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

 $data = 'info=.';
 $ch = curl_init();
 curl_setopt ($ch, CURLOPT_URL,"$host/api/showall?".$data);
 #curl_setopt ($ch, CURLOPT_URL,"$host/api/reload?".$data);
 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);                                                                                                              
 
$result = curl_exec($ch);
 curl_close($ch);
var_dump($result);
$i = json_decode($result, TRUE);

echo $i["result"];
