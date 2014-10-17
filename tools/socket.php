<?php
$socket = fsockopen("localhost", 50000);

if(!$socket)return;
stream_set_blocking($socket, 0);
stream_set_blocking(STDIN, 0);

do {
	$read   = array( $socket, STDIN); $write  = NULL; $except = NULL;

	if(!is_resource($socket)) return;
	$num_changed_streams = @stream_select($read, $write, $except, null);
	if(feof($socket)) return ;
	if($num_changed_streams  === 0) continue;
	if (false === $num_changed_streams) {
		/* Error handling */
		var_dump($read);
		echo "Continue\n";
		die;
	} elseif ($num_changed_streams > 0) {
		echo "\r";
		$data = trim(fgets($socket, 4096));
		if($data != "") {
			$dataParts = explode(':', $data);
   			$e = json_encode($dataParts);
                        //var_dump($dataParts);
			echo $e;
		}
	}

} while(true);
?>

