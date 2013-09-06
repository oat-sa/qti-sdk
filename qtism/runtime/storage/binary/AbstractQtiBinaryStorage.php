<?php

namespace qtism\runtime\storage\binary;

use qtism\runtime\tests\AssessmentItemSessionStore;
use qtism\runtime\tests\Route;
use qtism\data\Document;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\runtime\storage\common\StorageException;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\AssessmentTest;
use qtism\runtime\storage\common\AbstractStorage;
use \Exception;
use \RuntimeException;
use \InvalidArgumentException;

/**
 * An abstract Binary AssessmentTestSession Storage Service implementation.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractQtiBinaryStorage extends AbstractStorage {
    
    /**
     * The AssessmentTestSeeker object used by this implementation.
     * 
     * @var AssessmentTestSeeker
     */
    private $seeker;
    
    /**
     * Create a new AbstractQtiBinaryStorage.
     * 
     * @param AssessmentTest $assessmentTest An AssessmentTest object implementing the Document interface.
     * @throws InvalidArgumentException If $assessmentTest does not implement the Document interface.
     */
    public function __construct(AssessmentTest $assessmentTest) {
        parent::__construct($assessmentTest);
        
        $seekerClasses = array('assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration',
                                'responseDeclaration', 'branchRule', 'preCondition');
        
        $this->setSeeker(new AssessmentTestSeeker($this->getAssessmentTest(), $seekerClasses));
    }
    
    /**
     * Get the AssessmentTestSeeker object used by this implementation.
     * 
     * @param AssessmentTestSeeker $seeker An AssessmentTestSeeker object.
     */
    protected function setSeeker(AssessmentTestSeeker $seeker) {
        $this->seeker = $seeker;
    }
    
    /**
     * Set the AssessmentTestSeeker object used by this implementation.
     * 
     * @return AssessmentTestSeeker An AssessmentTestSeeker object.
     */
    protected function getSeeker() {
        return $this->seeker;
    }
    
    /**
     * Set the AssessmentTest definition on which the AbstractQtiBinaryStorage focuses on
     * to instantiate, retrieve and persist AssessmentTestSession objects.
     * 
     * Because this Storage implementation needs to identify in a unique manner the AssessmentTest he focuses
     * on, it only accepts AssessmentTest objects implementing the Document interface which provides the URI
     * of the AssessmentTest definition.
     * 
     * @param AssessmentTest An AssessmentTest object which implements the Document interface.
     * @throws InvalidArgumentException If $assessmentTest does not implement the Document interface.
     */
    protected function setAssessmentTest(AssessmentTest $assessmentTest) {
        if ($assessmentTest instanceof Document === false) {
            $msg = "This AssessmentTestSession Storage Service implementation only accepts to use ";
            $msg.= "AssessmentTest definition implementing the Document interface.";
            throw new InvalidArgumentException($msg);
        }
        else {
            // Accepted!
            parent::setAssessmentTest($assessmentTest);
        }
    }
    
    /**
     * Instantiate a new AssessmentTestSession.
     * 
     * @param string $sessionId An session ID. If not provided, a new session ID will be generated and given to the AssessmentTestSession.
     * @return AssessmentTestSession An AssessmentTestSession object.  
     */
    public function instantiate($sessionId = '') {
        
        // If not provided, generate a session ID.
        if (empty($sessionId) === true) {
            $sessionId = uniqid('qtism', true);
        }
        
        try {
            $session = AssessmentTestSession::instantiate($this->getAssessmentTest());
            $session->setSessionId($sessionId);
            
            return $session;
        }
        catch (Exception $e) {
            $msg = "An error occured while instantiating the given AssessmentTest.";
            throw new StorageException($msg, StorageException::INSTANTIATION, $e);
        }
    }
    
    public function persist(AssessmentTestSession $assessmentTestSession) {
        
        parent::persist($assessmentTestSession);
        
        try {
            
            $stream = new BinaryStream();
            $stream->open();
            $access = new QtiBinaryStreamAccess($stream);
            
            $access->writeTinyInt($assessmentTestSession->getState());
            
            $route = $assessmentTestSession->getRoute();
            $access->writeTinyInt($route->getPosition());
            
            // Persist the Route of the AssessmentTestSession and the related item sessions.
            $itemSessionStore = $assessmentTestSession->getAssessmentItemSessionStore();
            
            foreach ($route as $routeItem) {
                $access->writeRouteItem($this->getSeeker(), $routeItem);
            
                $itemSession = $itemSessionStore->getAssessmentItemSession($routeItem->getAssessmentItemRef(), $routeItem->getOccurence());
                $access->writeAssessmentItemSession($this->getSeeker(), $itemSession);
            }
            
            $this->persistStream($assessmentTestSession, $stream);
            
            $stream->close();
        }
        catch (Exception $e) {
            $sessionId = $assessmentTestSession->getSessionId();
            $msg = "An error occured while persisting AssessmentTestSession with ID '${sessionId}'.";
            throw new StorageException($msg, StorageException::PERSITANCE, $e);
        }
    }
    
    /**
     * Retrieve an AssessmentTestSession object from storage by $sessionId.
     * 
     * @return AssessmentTestSession An AssessmentTestSession object.
     * @throws StorageException If the AssessmentTestSession could not be retrieved from storage.
     */
    public function retrieve($sessionId) {
        
        try {
            
            $stream = $this->getRetrievalStream($this->getAssessmentTest(), $sessionId);
            $stream->open();
            $access = new QtiBinaryStreamAccess($stream);
            
            $assessmentTestSessionState = $access->readTinyInt();
            $currentPosition = $access->readTinyInt();
            
            // build the route and the item sessions.
            $route = new Route();
            $itemSessionStore = new AssessmentItemSessionStore();
            
            while ($stream->eof() === false) {
                
                $routeItem = $access->readRouteItem($this->getSeeker());
                $itemSession = $access->readAssessmentItemSession($this->getSeeker());
            
                $route->addRouteItemObject($routeItem);
                $itemSessionStore->addAssessmentItemSession($itemSession, $routeItem->getOccurence());
            }
            
            $route->setPosition($currentPosition);
            $assessmentTestSession = new AssessmentTestSession($this->getAssessmentTest(), $route);
            $assessmentTestSession->setAssessmentItemSessionStore($itemSessionStore);
            $assessmentTestSession->setSessionId($sessionId);
            $assessmentTestSession->setState($assessmentTestSessionState);
            
            $stream->close();
            
            return $assessmentTestSession;
        }
        catch (Exception $e) {
            $assessmentTestUri = $this->getAssessmentTest()->getUri();
            $msg = "An error occured while retrieving AssessmentTestSession for AssessmentTest '${assessmentTestUri}'.";
            throw new StorageException($msg, StorageException::RETRIEVAL, $e);
        }
    }
    
    /**
     * Get the BinaryStream that has to be used to retrieve an AssessmentTestSession.
     * 
     * Be careful, the implementation of this method must not open the given $stream.
     * 
     * @param string $sessionId A test session identifier.
     * @throws RuntimeException If an error occurs.
     * @return BinaryStream A BinaryStream object.
     */
    abstract protected function getRetrievalStream(Document $assessmentTest, $sessionId);
    
    /**
     * Persist A BinaryStream that contains the binary data representing $assessmentTestSession
     * in an appropriate location.
     * 
     * Be careful, the implementation of this method must not close the given $stream.
     * 
     * @param AssessmentTestSession $assessmentTestSession An AssessmentTestSession object.
     * @param BinaryStream $stream An open BinaryStream object.
     */
    abstract protected function persistStream(AssessmentTestSession $assessmentTestSession, BinaryStream $stream);
}