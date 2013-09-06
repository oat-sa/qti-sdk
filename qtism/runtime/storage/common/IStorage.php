<?php

namespace qtism\runtime\storage\common;

use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\AssessmentTest;

/**
 * The IStorage interface is implemented by any class that claims to 
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
interface IStorage {
    
    /**
     * Instantiate an AssessmentTestSession from the $assessmentTest AssessmentTest
     * definition. An AssessmentTestSession object is returned, with a session ID that will
     * make client code able to retrive persisted AssessmentTestSession objects later on.
     * 
     * If $sessionId is not provided, the AssessmentTestSession Storage Service implementation
     * must generate its own session ID.
     * 
     * @param AssessmentTest $assessmentTest An AssessmentTest object.
     * @param string $sessionId (optional) A wanted $sessionId to be used to identify the instantiated AssessmentTest.
     * @return AssessmentTestSession The AssessmentTestSession object built from $assessmentTest.
     * @throws StorageException If an error occurs while instantiating the AssessmentTest definition.
     */
    public function instantiate(AssessmentTest $assessmentTest, $sessionId = '');
    
    /**
     * Persist an AssessmentTestSession object for a later retrieval thanks to its
     * session ID.
     * 
     * @param AssessmentTestSession $assessmentTest An AssessmentTestSession object to be persisted.
     * @throws StorageException If an error occurs while persisting the $assessmentTest.
     */
    public function persist(AssessmentTestSession $assessmentTest);
    
    /**
     * Retrieve a previously persisted AssessmentTestSession object from its $assessmentTest
     * AssessmentTest definition and its $sessionId.
     * 
     * @param AssessmentTest $assessmentTest An AssessmentTest object.
     * @param string $sessionId The Session ID of the AssessmentTestSession to be retrieved.
     * @throws StorageException If an error occurs while retrieving the AssessmentTestSession corresponding to $assessmentTest and $sessionId.
     */
    public function retrieve(AssessmentTest $assessmentTest, $sessionId);
}