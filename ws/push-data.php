<?php
require __DIR__ . '/vendor/autoload.php';

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://127.0.0.1:5555");       //my domain, still using port 5555 as in their example


$socket1 = fsockopen("localhost", 50000);
if(!$socket1)return;
stream_set_blocking($socket1, 0);
stream_set_blocking(STDIN, 0);
$DEV='access_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6ImVlODNjMGNkNTAyOTMzMGNhODZmMWExYTE0OTMyNTFkOGM2OThmYTIiLCJjbGllbnRfaWQiOiJDTElFTlRfSUQxMSIsInVzZXJfaWQiOiIxIiwiZXhwaXJlcyI6MTQxMzc2MTUxMiwidG9rZW5fdHlwZSI6ImJlYXJlciIsInNjb3BlIjoidGVzdF9hZG1pbiBtYWluIn0.nQTOoBz5nX1z8CvEYSt7cVzrpHw0Xzvmk8GPfHZMsQwglcC8plJdWQCVx3pBRRkmHubMOfueWVPAP1zFiNRhjxIrnqUkjTORW9HEcYwmIaD_Ef4qbAgL-Ybo9_6s555PVs-mwdAkzNXkTTXfZdAZmisUACbZZKMtAZhzYD2JnSd9SWRPL4Zy_q0QOka7E_hPhQIynjO2mYshz-YBIPQL5JIMzuAwulIcZb4ioWXJhcZRhjEGSctgsFvl7Kz0QkaBtbmvieKzjesNJMI2EGU2TF6DdXcSzDjZd2ZVV3n4svIXGzyLuKYfwjPtpUE3gNi_NubkVCBGshdoozid8b-OUQ&device=testdev';
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
			    //'catecory' => 'testdev',
			    'catecory' => $DEV,
			    'a'   => $dataParts[0],
			    'b' => $dataParts[1],
			    'c' => $dataParts[2],
			    'when'    => time()
			);

			$socket->send(json_encode($entryData));
                }
        }

} while(true);

