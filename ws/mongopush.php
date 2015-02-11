<?php
require __DIR__ . '/vendor/autoload.php';

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://127.0.0.1:5556");
// apo dw ksekinaei
/*
// gia test apo edo 
                $socket->send("Continue\n");
                        $entryData = array(
                            'catecory' => 'testdev',
                            //'catecory' => $DEV,
                            'a'   => "1",
                            'when'    => time()
);
                        $socket->send(json_encode($entryData));
// test 
*/
/*
// edo vasete to port tou device pou thelete na akoussete
// pio device pesi se pia porta to vriskoume me
// ps aux | grep php
// vrite ena pou den to chrissimopioun
// kai trexte afto edo to archeio me tin katallili porta
p.x. $socket1 = fsockopen("localhost", 50018);
*/
$socket1 = fsockopen("localhost",50036 );
$devID='devID'.'xx'; //Device's Id .This will be the name of it's mongo collection

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
                //var_dump($read);
                $socket->send("Continue\n");
                die;
        } elseif ($num_changed_streams > 0) {
                echo "\r";
                $data = trim(fgets($socket1, 4096));
          if($data != "") {
// edo ftiachete ta data pou erchonte apo to device
                $length = strlen($data);
                $pos_a = strpos($data, "@");
                $pos_b = strpos($data, "#");


                if (count(explode('@', $data)) > 2) {
                        continue ;
                }
                elseif( ($pos_a === 0) && ($pos_b > 0)){
                        $data = substr($data, 1, $length-2);
                }elseif($pos_a === 0){
                        $data_a = substr($data, $pos_a+1);
                        continue;
                }
                elseif( $pos_b !== false ) $data = $data_a.substr($data, 0, $pos_b);
                else $data = $data_a.$data;

		 $entryData = array(
                                'data'   => $data,
                                'when'    => microtime('get_as_float'),
                                'devID' =>  $devID
                        );

                        //edo ta stelneis gia na egrafoun
                        // choris kamia kathisterissi
                        // ta archeia ta ipodechete to mongodb.php
                        // pou einai kai o pipe server gia tin mongo
                        $socket->send(json_encode($entryData));



           }
     }
} while(true);

