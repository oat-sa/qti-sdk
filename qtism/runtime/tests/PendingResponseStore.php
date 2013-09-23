<?php

namespace qtism\runtime\tests;

use qtism\data\AssessmentItemRef;
use \SplObjectStorage;

/**
 * The PendingResponseStore aims at storing PendingResponses. It's main goal
 * is to offer a clean API to add and retrieve PendingResponses objects depending
 * on the context.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PendingResponseStore {
    
    /**
     * A map of arrays indexed by AssessmentItemRef objects.
     * 
     * @var SplObjectStorage
     */
    private $assessmentItemRefMap;
    
    public function __construct() {
        $this->setAssessmentItemRefMap(new SplObjectStorage());
    }
    
    /**
     * Get the AssessmentItemRef map.
     * 
     * @return SplObjectStorage
     */
    protected function getAssessmentItemRefMap() {
        return $this->assessmentItemRefMap;
    }
    
    /**
     * Set the AssessmentItemRef map.
     * 
     * @param SplObjectStorage $assessmentItemRefMap
     */
    protected function setAssessmentItemRefMap(SplObjectStorage $assessmentItemRefMap) {
        $this->assessmentItemRefMap = $assessmentItemRefMap;
    }
    
    /**
     * Get all the PendingResponses objects held by the store.
     * 
     * @return PendingResponsesCollection A collection of PendingResponses objects held by the store.
     */
    public function getAllPendingResponses() {
        $collection = new PendingResponsesCollection();
        $map = $this->getAssessmentItemRefMap();
        foreach ($map as $itemRef) {
            foreach ($map[$itemRef] as $v) {
                $collection[] = $v;
            }
        }
        
        return $collection;
    }
    
    /**
     * Add a PendingResponse object to the store.
     * 
     * @param PendingResponses $pendingResponses
     */
    public function addPendingResponses(PendingResponses $pendingResponses) {
        $map = $this->getAssessmentItemRefMap();
        $itemRef = $pendingResponses->getAssessmentItemRef();
        
        if (isset($map[$itemRef]) === false) {
            $map[$itemRef] = array();
        }
        
        $entry = $map[$itemRef];
        $entry[$pendingResponses->getOccurence()] = $pendingResponses;
        $map[$itemRef] = $entry;
        
        $this->getAllPendingResponses()->attach($pendingResponses);
    }
    
    /**
     * Whether the store holds a PendingResponses object related to $assessmentItemRef and
     * $occurence.
     * 
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     * @param integer $occurence An occurence number.
     */
    public function hasPendingResponses(AssessmentItemRef $assessmentItemRef, $occurence = 0) {
        $map = $this->getAssessmentItemRefMap();
        return isset($map[$assessmentItemRef]) && isset($map[$assessmentItemRef][$occurence]);
    }
    
    /**
     * Get the PendingResponses object related to $assessmentItemRef.
     * 
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     * @param integer $occurence An occurence number.
     * @return false|PendingResponses
     */
    public function getPendingResponses(AssessmentItemRef $assessmentItemRef, $occurence = 0) {
        $returnValue = false;
        
        if ($this->hasPendingResponses($assessmentItemRef, $occurence)) {
            $map = $this->getAssessmentItemRefMap();
            $returnValue = $map[$assessmentItemRef][$occurence];
        }
        
        return $returnValue;
    }
}