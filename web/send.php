<?php
    // post.php ???
    // This all was here before  ;)
    $entryData = array(
        'category' => 'cat'
      , 'title'    => 'title'
      , 'article'  => 'article'
      , 'when'     => time()
    );


    // This is our new stuff
    $context = new ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'cat');
    $socket->connect("tcp://localhost:5555");

    $socket->send(json_encode($entryData));
