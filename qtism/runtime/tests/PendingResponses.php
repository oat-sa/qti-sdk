<?php

namespace qtism\runtime\tests;

use qtism\data\AssessmentItemRef;
use qtism\runtime\common\State;
use \InvalidArgumentException;

/**
 * The PendingResponses class represents a set of responses that have to be processed
 * later on e.g. in simultaneous submission mode.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PendingResponses {
    
    /**
     * A State object.
     * 
     * @var State
     */
    private $state;
    
    /**
     * The AssessmentItemRef object related to the State object.
     * 
     * @var AssessmentItemRef
     */
    private $assessmentItemRef;
    
    /**
     * The occurence number of the AssessmentItemRef object related to the State.
     * 
     * @var integer
     */
    private $occurence;
    
    /**
     * Create a new PendingResponses object.
     * 
     * @param State $state The ResponseState object that represent the pending responses.
     * @param AssessmentItemRef $assessmentItemRef The AssessmentItemRef the pending responses are related to.
     * @param integer $occurence The occurence number of the item the pending responses are related to.
     */
    public function __construct(State $state, AssessmentItemRef $assessmentItemRef, $occurence) {
        $this->setState($state);
        $this->setAssessmentItemRef($assessmentItemRef);
        $this->setOccurence($occurence);
    }
    
    /**
     * Set the State object that represent the pending responses.
     * 
     * @param State $state A State object.
     */
    public function setState(State $state) {
        $this->state = $state;
    }
    
    /**
     * Get the State object that represent the pending responses.
     * 
     * @return State A State object.
     */
    public function getState() {
        return $this->state;
    }
    
    /**
     * Set the AssessmentItemRef object related to the State object.
     * 
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     */
    public function setAssessmentItemRef(AssessmentItemRef $assessmentItemRef) {
        $this->assessmentItemRef = $assessmentItemRef;
    }
    
    /**
     * Get the AssessmentItemRef object related to the State object.
     * 
     * @return AssessmentItemRef An AssessmentItemRef object.
     */
    public function getAssessmentItemRef() {
        return $this->assessmentItemRef;
    }
    
    /**
     * Set the occurence number of the AssessmentItemRef object related to the State.
     * 
     * @param integer $occurence An occurence number as a positive integer.
     * @throws InvalidArgumentException If $occurence is not a postive integer.
     */
    public function setOccurence($occurence) {
        if (gettype($occurence) !== 'integer') {
            $msg = "The 'occurence' argument must be an integer value, '" . gettype($occurence) . "' given.";
            throw new InvalidArgumentException($msg);
        }
        else {
            $this->occurence = $occurence;
        }
    }
    
    /**
     * Get the occurence number of the AssessmentItemRef object related to the State.
     * 
     * @return integer A postivie integer value.
     */
    public function getOccurence() {
        return $this->occurence;
    }
}