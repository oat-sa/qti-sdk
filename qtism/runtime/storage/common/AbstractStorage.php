<?php

namespace qtism\runtime\storage\common;

use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\AssessmentTest;
use \LogicException;

/**
 * The AbstractStorage class is extended by any class that claims to 
 * offer an AssessmentTestSession Storage Service.
 * 
 * An AssessmentTestSession Storage Service must be able to:
 * 
 * * Instantiate an AssessmentTestSession from its AssessmentTest definition.
 * * Persist an AssessmentTestSession for a later retrieval.
 * * Retrieve an AssessmentTestSession.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractStorage {
    
    /**
     * The AssessmentTest definition on which the AssessmentTestSession Storage Service
     * focuses on.
     * 
     * @var AssessmentTest
     */
    private $assessmentTest;
    
    /**
     * Create a new AbstracStorage object.
     * 
     * @param AssessmentTest $assessmentTest The AssessmentTest definition that is used by the storage implementation as a pattern.
     */
    public function __construct(AssessmentTest $assessmentTest) {
        $this->setAssessmentTest($assessmentTest);
    }
    
    /**
     * Set the AssessmentTest object that the AssessmentTestSession Storage Service
     * uses as a pattern for instantiation, retrieval and persistance.
     * 
     * @param AssessmentTest $assessmentTest An AssessmentTest object.
     */
    protected function setAssessmentTest(AssessmentTest $assessmentTest) {
        $this->assessmentTest = $assessmentTest;
    }
    
    /**
     * Get the AssessmentTest object that the AssessmentTestSession Storage Service
     * uses as a pattern for instantiation, retrieval and persistance.
     *
     * @return AssessmentTest An AssessmentTest object.
     */
    protected function getAssessmentTest() {
        return $this->assessmentTest;
    }
    
    /**
     * Instantiate an AssessmentTestSession from the $assessmentTest AssessmentTest
     * definition. An AssessmentTestSession object is returned, with a session ID that will
     * make client code able to retrive persisted AssessmentTestSession objects later on.
     * 
     * If $sessionId is not provided, the AssessmentTestSession Storage Service implementation
     * must generate its own session ID.
     * 
     * @param string $sessionId (optional) A wanted $sessionId to be used to identify the instantiated AssessmentTest.
     * @throws StorageException If an error occurs while instantiating the AssessmentTest definition.
     */
    abstract public function instantiate($sessionId = '');
    
    /**
     * Persist an AssessmentTestSession object for a later retrieval thanks to its
     * session ID.
     * 
     * @param AssessmentTestSession $assessmentTestSession An AssessmentTestSession object to be persisted.
     * @throws StorageException If an error occurs while persisting the $assessmentTestSession.
     * @throws LogicException 
     */
    public function persist(AssessmentTestSession $assessmentTestSession) {
        if ($assessmentTestSession->getAssessmentTest() !== $this->getAssessmentTest()) {
            $msg = "The AssessmentTestSession object to be persisted has not the same ";
            $msg.= "AssessmentTest definition than the one used by this AssessmentTestSession ";
            $msg.= "Storage Implementation.";
            
            throw new LogicException($msg);
        }
    }
    
    /**
     * Retrieve a previously persisted AssessmentTestSession object.
     * 
     * @param string $sessionId The Session ID of the AssessmentTestSession to be retrieved.
     * @throws StorageException If an error occurs while retrieving the AssessmentTestSession.
     */
    abstract public function retrieve($sessionId);
}