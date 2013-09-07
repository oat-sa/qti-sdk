<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\Pair;
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
        
        $storage = new TemporaryQtiBinaryStorage($doc);
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
        
        // Q01 - Correct response.
        $this->assertInternalType('float', $session['Q01.scoring']);
        $this->assertEquals(0.0, $session['Q01.scoring']);
        $this->assertSame(null, $session['Q01.RESPONSE']);
        
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'))));
        
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
        $session->skip();
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        
        // Q04 - Correct response.
        $this->assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals('S02', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
    }
    
}