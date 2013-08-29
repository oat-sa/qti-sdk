<?php

namespace qtism\runtime\tests;

use \Exception;

/**
 * The AssessmentTestSessionException must be thrown when an error occurs
 * in an AssessmentTestSession.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestSessionException extends Exception {
    
    /**
     * Code to use when the origin of the error is unknown.
     * 
     * @var integer
     */
    const UNKNOWN = 0;
    
    /**
     * Code to use when a state violation occurs e.g. while trying
     * to skip the current item but the test session is closed.
     * 
     * @var integer
     */
    const STATE_VIOLATION = 1;
    
    /**
     * Code to use when a navigation mode violation occurs e.g. while
     * trying to move to the next item but the navigation is LINEAR.
     * 
     * @var unknown_type
     */
    const NAVIGATION_MODE_VIOLATION = 2;
    
    /**
     * Code to use when an error occurs while running the outcome processing
     * relate to the AssessmentTest.
     * 
     * @var int
     */
    const OUTCOME_PROCESSING_ERROR = 3;
    
    /**
     * Create a nex AssessmentTestSessionException.
     * 
     * @param string $message A human-readable message describing the error.
     * @param integer $code A code to enable client-code to identify the error programatically.
     * @param Exception $previous An optional previous exception.
     */
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    
}