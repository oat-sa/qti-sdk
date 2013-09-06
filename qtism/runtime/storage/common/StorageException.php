<?php

namespace qtism\runtime\storage\common;

use \Exception;

/**
 * The StorageException class represents exceptions that AssessmentTestSession
 * Storage Services encounter an error.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StorageException extends Exception {
    
    /**
     * Create a new StorageException instance.
     * 
     * @param string $message A human-readable message describing the encountered error.
     * @param integer $code A code enabling client-code to identify the cause of the error.
     * @param Exception $previous An optional previous Exception that was thrown and catched.
     */
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    
}