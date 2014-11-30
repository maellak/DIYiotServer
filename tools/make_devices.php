#!/usr/bin/php
<?php

include("full path for core.php");
$dbfile = 'full path oauth.sqlite';

$home = diyConfig::read('ssh.home');
$_keys = diyConfig::read('ssh.keys');

$db = new PDO(sprintf('sqlite:%s', $dbfile));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare('SELECT * FROM oauth_clients');
$stmt->execute();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo $row["apiport"]."\n";
	if($row["apiport"]){
		$devuser=trim($row["client_id"]);
		$apiport=trim($row["apiport"]);
		$dataport=trim($row["dataport"]);
		$sshhome = $home."/$devuser/$_keys";
		echo $sshhome;
		if (file_exists($sshhome)) {
		    //echo "The file $sshhome exists";
		}else{
				$tmp = "/tmp/diy-$devuser.pem";
				$output=shell_exec("echo -e  'y\n' | ssh-keygen -q -N '' -f $tmp");
				$devkey1 = file_get_contents("$tmp.pub");
				$devkey1 = trim($devkey1);
				$devkey2 = file_get_contents("$tmp");
				$auth_settings = 'no-pty,no-X11-forwarding,permitopen="localhost:'.$dataport.'",permitopen="localhost:'.$apiport.'",command="/bin/echo do-not-send-commands" '.$devkey1;
				mkdir("$home/$devuser");
				file_put_contents("$home/$devuser/$_keys",$auth_settings);
				exec("adduser -U $devuser -s /bin/true");	
				exec("chmod 700  $home/$devuser");
				exec("chmod 644  $home/$devuser/$_keys");
				exec("chown $devuser.$devuser  $home/$devuser");
				exec("chown $devuser.$devuser  $home/$devuser/$_keys");
                        	$stmt12 = $db->prepare('UPDATE oauth_devices  set public_key = :devkey1, private_key = :devkey2 where device = :devuser');
                        	$stmt12->execute(array('devkey1' => $devkey1, 'devkey2' => $devkey2, 'devuser' => $devuser));

		}
	}
}




