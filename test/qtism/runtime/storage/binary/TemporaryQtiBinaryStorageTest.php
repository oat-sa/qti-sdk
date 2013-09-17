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
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;
use qtism\runtime\storage\binary\TemporaryQtiBinaryStorage;

class TemporaryQtiBinaryStorageTest extends QtiSmTestCase {
    
    public function testTemporaryQtiBinaryStorage() {
        
        $doc = new XmlCompactAssessmentTestDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        $seekerClasses = array('assessmentItemRef', 'assessmentSection', 'testPart', 
                                'branchRule', 'preCondition', 'outcomeDeclaration', 'responseDeclaration');
        $seeker = new AssessmentTestSeeker($doc, $seekerClasses);
        
        $testSessionFactory = new AssessmentTestSessionFactory($doc);
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
        $iterator = new QtiComponentIterator($doc, array('assessmentItemRef'));
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
        foreach ($doc->getOutcomeDeclarations() as $outcomeDeclaration) {
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
    
}