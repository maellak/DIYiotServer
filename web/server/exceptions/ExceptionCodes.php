<?php

/**
 *
 * @version 1.0
 * @author  diyiot
 * @package Exceptions
 */

header("Content-Type: text/html; charset=utf-8");

/** 
 * Κωδικοί Σφαλμάτων
 * 
 * Παρακάτω εμφανίζονται οι Κωδικοί Σφαλμάτων που διαχειρίζετε η <a href="class-CustomException.html">CustomException</a>
 * 
 */

class ExceptionCodes
{  
    //general messages 
    
        const NoErrors = 200;
        const MethodNotFound = 500;
        const FunctionNotFound = 500;
        const UserNotFound = 500;
        const ScopeNotFound = 500;
        const DeviceExist = 500;
        const OrgExist = 500;
}

?>
