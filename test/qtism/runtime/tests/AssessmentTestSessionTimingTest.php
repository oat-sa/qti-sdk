<?php

use qtism\common\datatypes\Point;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\Duration;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionFactory;
use qtism\data\storage\xml\XmlCompactDocument;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class AssessmentTestSessionTimingTest extends QtiSmTestCase {
    
    public function testTestPartAssessmentSectionsDurations() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
         
        $factory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
        $session = AssessmentTestSession::instantiate($factory);
         
        // Try to get a duration on a non-begun test session.
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT0S')));
         
        $session->beginTestSession();
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT0S')));
         
        // Q01.
        $session->beginAttempt();
        sleep(1);
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'))));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT1S')));
         
        // Q02.
        $session->beginAttempt();
        sleep(1);
        $session->skip();
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT2S')));
         
        // Try to get a duration that does not exist.
        $this->assertSame(null, $session['P02.duration']);
    }
    
    public function testTestPartTimeLimitsLinear() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/timelimits_testparts_linear_individual.xml');
         
        $factory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
        $session = AssessmentTestSession::instantiate($factory);
        $session->beginTestSession();
         
        // Q01.
        $session->beginAttempt();
        sleep(2);
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'))));
        $this->assertTrue($session->getRemainingTimeTestPart()->equals(new Duration('PT3S')));
         
        // Q02.
        $session->beginAttempt();
        sleep(2);
        $session->updateDuration();
        $this->assertTrue($session->getRemainingTimeTestPart()->equals(new Duration('PT1S')));
        $session->skip();
         
        // Q03.
        $session->beginAttempt();
        sleep(2);
         
        try {
            // P01.duration = 6 > maxTime -> exception !
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array('H', 'O'))))));
            $this->assertFalse(true);
        }
        catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::TEST_PART_DURATION_OVERFLOW, $e->getCode());
        }
         
        // We should have automatically be moved to the next test part.
        $this->assertEquals('P02', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertTrue($session->getRemainingTimeTestPart()->equals(new Duration('PT1S')));
         
        // Q04.
        $session->beginAttempt();
        sleep(2);
         
        try {
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))));
            $this->assertTrue(false);
        }
        catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::TEST_PART_DURATION_OVERFLOW, $e->getCode());
        }
         
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertFalse($session->getCurrentAssessmentItemRef());
         
        // Ok with outcome processing?
        $this->assertEquals(1, $session['NRESPONSED']);
    }
}