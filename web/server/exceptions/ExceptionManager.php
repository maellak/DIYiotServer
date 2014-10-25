<?php

/**
 *
 * @version 1.0
 * @author  diyiot
 * @package Exceptions
 */

class CustomException extends Exception{

    protected $title;

    public function __construct($message, $code = 0, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

}
?>
