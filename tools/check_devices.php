<?php

$dbfile = '../db/oauth.sqlite';


$db = new PDO(sprintf('sqlite:%s', $dbfile));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare('SELECT * FROM oauth_clients');
$stmt->execute();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($row["apiport"]){
		$port=$row["apiport"];
   		$par="./isAlive.php -p$port &";
                exec($par);
	}
}




