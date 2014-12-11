#!/usr/bin/php
<?php

include("myhost.php");
include("rdir.php");

$par  =  "s:";
$par  .=  "l:";
$par  .=  "d:";
$par  .=  "f:";
$par  .=  "c:";
$par  .=  "w:";
$options = getopt($par);
$srcfile = trim($options["s"]);
$srclib = trim($options["l"]);
$device = trim($options["d"]);
$filename = trim($options["f"]);
$comp = trim($options["c"]);
$writedevice = trim($options["w"]);

$info = <<<EOD

client-compile.php  

    INFO: compile sketch for a device
    OPTIONS:
	-s source file
	-l directory with lib 
	-d device name
	-f filename
	-c gcc/ino
	-w yes/no


EOD;
if(!($options['s'] || $options['l'] || $options['d'] || $options["f"] || $options["c"] || $options["w"]) ){
	echo $info;
	die;
}
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
$dir = realpath($srclib);
$getcwd = getcwd();
chdir($dir);
$srclibarray = read_all_files();
chdir($getcwd);
 $data1 = 'access_token='.$curlResponse['access_token'].'&test=test';
 $data1 .= '&device='.$device;
 $data1 .= '&srcfile='.urlencode(base64_encode(urlencode(file_get_contents($srcfile))));
 if(count($srclibarray['files']) > 0) {
    $fixedFiles = array();
    foreach($srclibarray['files'] as $curName => $curFile) {
        $fixedFiles[] = 'srclib['.$curName.']='.urlencode($curFile);
    }
    $data1 .= '&'.implode('&', $fixedFiles);
 }
 $data1 .= '&filename='.$filename;
 $data1 .= '&comp='.$comp;
 $data1 .= '&writedevice='.$writedevice;


 $ch = curl_init();
 curl_setopt ($ch, CURLOPT_URL,"$host/api/compile");
 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);                                                                                                              
 curl_setopt ($ch, CURLOPT_POSTFIELDS, $data1);
 curl_setopt ($ch, CURLOPT_POST, 1);
 
$result = curl_exec($ch);
echo " --------------------------------------------\n\n";
var_dump($result);
 $r = json_decode($result, TRUE);
var_dump($r);
echo " --------------------------------------------\n\n";
echo $data1;
 curl_close($ch);
