<?php

namespace qtism\runtime\tests;

use \Exception;

/**
 * The OrderingException must be thrown when an error occurs while
 * ordering child elements of an AssessmentSection.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OrderingException extends Exception {
    
    /**
     * Error code to use when the nature
     * of the error is unknown.
     * 
     * @var integer
     */
    const UNKNOWN = 0;
    
    /**
     * Error code to use when the error comes
     * from a lack of logic.
     * 
     * @var integer
     */
    const LOGIC_ERROR = 1;
    
    /**
     * Create a new OrderingException exception object.
     * 
     * @param string $message A human-readable message describing the error while ordering child elements of an AssessmentSection.
     * @param integer $code The code that enables client-code to identify the nature of the error efficiently.
     * @param Exception $previous An optional previous Exception object.
     */
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}