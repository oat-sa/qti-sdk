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
     * @var integer
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
     * Code to use when an error occurs while running the response processing
     * related to a postponed response submission.
     * 
     * @var integer
     */
    const RESPONSE_PROCESSING_ERROR = 4;
    
    /**
     * Code to use when an error occurs while transmitting item/test results.
     * 
     * @var integer
     */
    const RESULT_SUBMISSION_ERROR = 5;
    
    /**
     * Error code to use when a logic error is done.
     * 
     * @var integer
     */
    const LOGIC_ERROR = 6;
    
    /**
     * Error code to use when some responses to items are missing
     * prior to go further in the AssessmentTestSession flow.
     * 
     * @var integer
     */
    const MISSING_RESPONSES = 7;
    
    /**
     * Error code to use when a jump is performed outside the current
     * TestPart.
     *
     * @var integer
     */
    const FORBIDDEN_JUMP = 8;
    
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