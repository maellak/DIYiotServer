#!/usr/bin/php
<?php

$par  =  "p:";
$options = getopt($par);
$port = trim($options["p"]);

$host="127.0.0.1:$port";
/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

 $data = 'info=.';
 $ch = curl_init();
 curl_setopt ($ch, CURLOPT_URL,"$host/api/isAlive?".$data);
 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($ch);
 curl_close($ch);

