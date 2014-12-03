#!/usr/bin/php
<?php

include("full path for web/server/system/core.php");
$dbfile = 'full path for db/oauth.sqlite';

/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);


$db = new PDO(sprintf('sqlite:%s', $dbfile));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare('SELECT * FROM oauth_clients');
$stmt->execute();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		//echo trim($row["dataport"]);
		//echo trim($row["client_id"]);
		//echo "\n";
	if($row["dataport"]){
		$devuser=trim($row["client_id"]);
		$dataport=trim($row["dataport"]);

		$output='';
		$netstat = 'netstat -apn | grep "^tcp " | grep '.$dataport;
		exec("$netstat 2>&1", $output, $return_var);
		//echo $netstat;
		//var_dump($output);
		if ($output ) {
			$output1='';
			$pgrep = 'ps -aux  | grep "datastart -p'.$dataport.'" | grep -v grep';
			exec("$pgrep 2>&1", $output1, $return_var);
			if(!$output1){
				echo $dataport;
				$datastart = "/var/www/diyiot/ws/datastart -p$dataport -d$devuser";
				//exec("$datastart 2>&1", $output, $return_var);
				echo $datastart;
				   $pid = pcntl_fork();

				    switch($pid) {
					case -1:
					    print "Could not fork!\n";
					    exit;
					case 0:
					    exec("$datastart 2>&1", $output, $return_var);
					    break;
					default:
					   echo "start";
				       }
				echo "\n";
			}
		}
	}
}




