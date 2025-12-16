<?php 
class DuplicateException extends Exception {
    public function __construct($message = "Duplicate entry found.", $code = 409, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }   
}



?>