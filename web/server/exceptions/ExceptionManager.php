<?php

class CustomException extends Exception{

    protected $title;

    public function __construct($message, $code = 0, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

}
?>