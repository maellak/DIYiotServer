<?php

/**
 *
 * @version 1.0
 * @author  diyiot
 * @package Exceptions
 */

header("Content-Type: text/html; charset=utf-8");

/** 
 * Μηνύματα Σφαλμάτων
 * 
 * Παρακάτω εμφανίζονται τα Μηνύματα Σφαλμάτων που διαχειρίζετε η {@see CustomException}
 * 
 */

class ExceptionMessages
{   
    //general messages=========================================================================================================================== 
    
        const NoErrors = 'success';
        const MethodNotFound = 'H μέθοδος δεν βρέθηκε';
        const FunctionNotFound = 'Η διαδικασία δεν βρέθηκε';
        const UserNotFound = 'Ο χρήστης δεν βρέθηκε';
        const ScopeNotFound = 'Το scope δεν βρέθηκε';
        const DeviceExist = 'Το Device υπάρχει';
}
   ?>
