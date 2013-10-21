<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\tests\AssessmentTestSessionFactory;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\Pair;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Point;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\data\QtiComponentIterator;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\storage\binary\TemporaryQtiBinaryStorage;

class TemporaryQtiBinaryStorageTest extends QtiSmTestCase {
    
    public function testTemporaryQtiBinaryStorage() {
        
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        $seekerClasses = array('assessmentItemRef', 'assessmentSection', 'testPart', 
                                'branchRule', 'preCondition', 'outcomeDeclaration', 'responseDeclaration');
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), $seekerClasses);
        
        $testSessionFactory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($testSessionFactory);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();
        
        $this->assertInstanceOf('qtism\\runtime\\tests\\AssessmentTestSession', $session);
        $this->assertEquals(AssessmentTestSessionState::INITIAL, $session->getState());
        
        $session->beginTestSession();
        $storage->persist($session, $seeker);
        
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
        
        // The test session has begun. We are in linear mode so that all
        // item sessions must be initialized and selected for presentation 
        // to the candidate.
        $itemSessionStore = $session->getAssessmentItemSessionStore();
        $itemSessionCount = 0;
        $iterator = new QtiComponentIterator($doc->getDocumentComponent(), array('assessmentItemRef'));
        foreach ($iterator as $itemRef) {
            $refItemSessions = $itemSessionStore->getAssessmentItemSessions($itemRef);
            foreach ($refItemSessions as $refItemSession) {
                $this->assertEquals(AssessmentItemSessionState::INITIAL, $refItemSession->getState());
                $itemSessionCount++;
            }
        }
        $this->assertEquals(9, $itemSessionCount);
        
        // The outcome variables composing the test-level global scope
        // must be set with their default value if any.
        foreach ($doc->getDocumentComponent()->getOutcomeDeclarations() as $outcomeDeclaration) {
        	$this->assertFalse(is_null($session[$outcomeDeclaration->getIdentifier()]));
        	$this->assertEquals(0, $session[$outcomeDeclaration->getIdentifier()]);	
        }
        
        // Q01 - Correct response.
        $this->assertInternalType('float', $session['Q01.scoring']);
        $this->assertEquals(0.0, $session['Q01.scoring']);
        $this->assertSame(null, $session['Q01.RESPONSE']);
        
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'))));
        
        // Because Q01 is not a multi-occurence item in the route, isLastOccurenceUpdate always return false.
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        
        $this->assertInternalType('float', $session['Q01.scoring']);
        $this->assertEquals(1.0, $session['Q01.scoring']);
        $this->assertEquals('ChoiceA', $session['Q01.RESPONSE']);
        
        // Q02 - Incorrect response.
        $this->assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals('S01', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $session->getCurrentTestPart()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('C', 'M')))))));
        
        $this->assertInternalType('float', $session['Q02.SCORE']);
        $this->assertEquals(1.0, $session['Q02.SCORE']);
        
        // Q03 - Skip.
        $session->beginAttempt();
        $session->skip();
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        
        // Q04 - Correct response.
        $this->assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals('S02', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('W', 'G1'), new DirectedPair('Su', 'G2')))))));
        $this->assertInternalType('float', $session['Q04.SCORE']);
        $this->assertEquals(3.0, $session['Q04.SCORE']);
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertTrue($session['Q04.RESPONSE']->equals(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('W', 'G1'), new DirectedPair('Su', 'G2')))));
        
        // Q05 - Skip.
        $session->beginAttempt();
        $session->skip();
        
        // Q06 - Skip.
        $session->beginAttempt();
        $session->skip();
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        
        // Q07.1 - Incorrect response (but inside the circle).
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(103, 114)))));
        // We now test the lastOccurence update for this multi-occurence item.
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));
        
        // Q07.2 Incorrect response (outside the circle).
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(1, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(200, 200)))));
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));
        
        // Q07.3 Correct response (perfectly on the point).
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))));
        
        // End of test, outcome processing performed.
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertInternalType('integer', $session['NCORRECTS01']);
        $this->assertEquals(1, $session['NCORRECTS01']);
        $this->assertInternalType('integer', $session['NCORRECTS02']);
        $this->assertEquals(1, $session['NCORRECTS02']);
        $this->assertInternalType('integer', $session['NCORRECTS03']);
        $this->assertEquals(1, $session['NCORRECTS03']);
        $this->assertEquals(6, $session['NINCORRECT']);
        $this->assertEquals(6, $session['NRESPONSED']);
        $this->assertEquals(9, $session['NPRESENTED']);
        $this->assertEquals(9, $session['NSELECTED']);
        $this->assertInternalType('float', $session['PERCENT_CORRECT']);
        $this->assertEquals(round(33.33333, 3), round($session['PERCENT_CORRECT'], 3));
    }
    
    public function testLinearNavigationSimultaneousSubmission() {
        
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');
        $factory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($factory);
        $sessionId = 'linearSimultaneous1337';
        $session = $storage->instantiate($sessionId);
        $session->beginTestSession();
        
        // Nothing in pending responses. The test has just begun.
        $this->assertEquals(0, count($session->getPendingResponseStore()->getAllPendingResponses()));
        
        // Q01 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'))));
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(1, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertEquals('ChoiceA', $session['Q01.RESPONSE']);
        $this->assertEquals(0.0, $session['Q01.scoring']);
        
        // Q02 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'P'), new Pair('C', 'M'), new Pair('D', 'L')))))));
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertTrue($session['Q02.RESPONSE']->equals(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'P'), new Pair('C', 'M'), new Pair('D', 'L')))));
        $this->assertEquals(0.0, $session['Q02.SCORE']);
        $this->assertEquals(2, count($session->getPendingResponseStore()->getAllPendingResponses()));
        
        // Q03 - Skip
        $session->beginAttempt();
        $session->skip();
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(3, count($session->getPendingResponseStore()->getAllPendingResponses()));
        
        // Q04 - Skip
        $session->beginAttempt();
        $session->skip();
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(4, count($session->getPendingResponseStore()->getAllPendingResponses()));
        
        // Q05 - Skip
        $session->beginAttempt();
        $session->skip();
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(5, count($session->getPendingResponseStore()->getAllPendingResponses()));
        
        // Q06 - Skip
        $session->beginAttempt();
        $session->skip();
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(6, count($session->getPendingResponseStore()->getAllPendingResponses()));
        
        // Q07.1 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))));
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(7, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertTrue($session['Q07.1.RESPONSE']->equals(new Point(102, 113)));
        $this->assertEquals(0.0, $session['Q07.1.SCORE']);
        
        // Q07.2 - Incorrect but in the circle
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(103, 114)))));
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertEquals(8, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertTrue($session['Q07.2.RESPONSE']->equals(new Point(103, 114)));
        $this->assertEquals(0.0, $session['Q07.2.SCORE']);
        
        // Q07.3 - Incorrect and out of the circle
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(30, 13)))));
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        
        // Response processing should have taken place beauce this is the end of the current test part.
        // The Pending Response Store should be then flushed and now empty.
        $this->assertEquals(0, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertTrue($session['Q07.3.RESPONSE']->equals(new Point(30, 13)));
        $this->assertEquals(0.0, $session['Q07.3.SCORE']);
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        
        // Let's check the overall Assessment Test Session state.
        $this->assertEquals(1.0, $session['Q01.scoring']);
        $this->assertEquals(4.0, $session['Q02.SCORE']);
        $this->assertInternalType('float', $session['Q03.SCORE']);
        $this->assertEquals(0.0, $session['Q03.SCORE']);
        $this->assertInternalType('float', $session['Q04.SCORE']);
        $this->assertEquals(0.0, $session['Q04.SCORE']);
        $this->assertInternalType('float', $session['Q05.SCORE']);
        $this->assertEquals(0.0, $session['Q05.SCORE']);
        $this->assertInternalType('float', $session['Q06.mySc0r3']);
        $this->assertEquals(0.0, $session['Q06.mySc0r3']);
        $this->assertEquals(1.0, $session['Q07.1.SCORE']);
        $this->assertEquals(1.0, $session['Q07.2.SCORE']);
        $this->assertInternalType('float', $session['Q07.3.SCORE']);
        $this->assertEquals(0.0, $session['Q07.3.SCORE']);
        $this->assertEquals(2, $session['NCORRECTS01']);
        $this->assertEquals(0, $session['NCORRECTS02']);
        $this->assertEquals(1, $session['NCORRECTS03']);
        $this->assertEquals(6, $session['NINCORRECT']);
        $this->assertEquals(5, $session['NRESPONSED']);
        $this->assertEquals(9, $session['NPRESENTED']);
        $this->assertEquals(9, $session['NSELECTED']);
        $this->assertEquals(round(33.33333, 3), round($session['PERCENT_CORRECT'], 3));
    }
    
    public function testAutoForward() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        $seekerClasses = array('assessmentItemRef', 'assessmentSection', 'testPart',
                        'branchRule', 'preCondition', 'outcomeDeclaration', 'responseDeclaration');
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), $seekerClasses);
        
        $testSessionFactory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($testSessionFactory);
        $session = $storage->instantiate();
        $session->beginTestSession();
        $sessionId = $session->getSessionId();
        
        $this->assertTrue($session->mustAutoForward());
        $session->setAutoForward(false);
        
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this->assertFalse($session->mustAutoForward());
    }
    
    public function testNonLinear() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/jumps.xml');
        $seekerClasses = array('assessmentItemRef', 'assessmentSection', 'testPart',
                        'branchRule', 'preCondition', 'outcomeDeclaration', 'responseDeclaration');
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), $seekerClasses);
        
        $testSessionFactory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($testSessionFactory);
        $session = $storage->instantiate();
        $session->beginTestSession();
        $sessionId = $session->getSessionId();
        
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        
    }
}