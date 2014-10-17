<?php
require __DIR__ . '/vendor/autoload.php';

$entryData = array(
    'catecory' => "kittensCategory",
    'title'   => "test",
    'article' => "hallo",
    'when'    => time()
);
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://127.0.0.1:5555");       //my domain, still using port 5555 as in their example

$socket->send(json_encode($entryData));

$socket1 = fsockopen("localhost", 50000);

if(!$socket1)return;
stream_set_blocking($socket1, 0);
stream_set_blocking(STDIN, 0);

do {
        $read   = array( $socket1, STDIN); $write  = NULL; $except = NULL;

        if(!is_resource($socket1)) return;
        $num_changed_streams = @stream_select($read, $write, $except, null);
        if(feof($socket1)) return ;
        if($num_changed_streams  === 0) continue;
        if (false === $num_changed_streams) {
                /* Error handling */
                //var_dump($read);
                $socket->send("Continue\n");
                die;
        } elseif ($num_changed_streams > 0) {
                echo "\r";
                $data = trim(fgets($socket1, 4096));
                if($data != "") {
                        $dataParts = explode(':', $data);
			$entryData = array(
			    'catecory' => "kittensCategory",
			    'a'   => $dataParts[0],
			    'b' => $dataParts[1],
			    'c' => $dataParts[2],
			    'when'    => time()
			);

			$socket->send(json_encode($entryData));
                }
        }

} while(true);

