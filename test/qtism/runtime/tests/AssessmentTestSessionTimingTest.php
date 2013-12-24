<?php

use qtism\runtime\tests\AssessmentItemSessionException;
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

require_once (dirname(__FILE__) . '/../../../QtiSmAssessmentTestSessionTestCase.php');

class AssessmentTestSessionTimingTest extends QtiSmAssessmentTestSessionTestCase {
    
    public function testTestPartAssessmentSectionsDurations() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/itemsubset.xml');
         
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
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timelimits_testparts_linear_individual.xml');
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
    
    /**
     * This test aims at testing if it is possible to force the attempt to be performed
     * event if time constraints in force are exceeded, by the use of the $allowLateSubmission
     * argument.
     * 
     */
    public function testForceLateSubmission($forceLateSubmission = true) {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/force_late_submission.xml');
        $session->beginTestSession();
        
        // outeach maxTime (1sec)
        $session->beginAttempt();
        sleep(2);
        
        try {
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, 'ChoiceA'))), $forceLateSubmission);
            
            $this->assertTrue($forceLateSubmission, '$forceLateSubmission is false but the attempt dit not raised an exception.');
            $this->assertEquals(1.0, $session['Q01.SCORE']);
            $this->assertInternalType('float', $session['Q01.SCORE']);
            $this->assertFalse($session->isRunning());
            
            // What if $forceLateSubmission = false ? :p
            if ($forceLateSubmission === true) {
                $this->testForceLateSubmission(false);
            }
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertFalse($forceLateSubmission, '$forceLateSubmission is true but the attempt should have been correctly ended.');
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
        }
    }
    
    /**
     * This test aims at testing that an exception is thrown if we move
     * to a next target item which is timed out.
     * 
     */
    public function testMoveNextTargetTimeout($allowTimeout = false) {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/move_next_target_timeout.xml');
        $session->beginTestSession();
        $this->assertTrue($session->mustAutoForward());
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        
        // Jump to the target item (the 2nd and last one) to outreach timings.
        $session->jumpTo(1);
        $session->beginAttempt();
        sleep(2);
        $session->moveBack();
        
        // Jump on a timed out item.
        try {
            $session->jumpTo(1, $allowTimeout);
            $this->assertTrue($allowTimeout);
            $this->assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        }
        catch (AssessmentTestSessionException $e) {
            $this->assertFalse($allowTimeout);
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_DURATION_OVERFLOW, $e->getCode());
            
            // We did not move then?
            $this->assertTrue($session->isRunning());
            $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        }
    }
}