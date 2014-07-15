<?php
use qtism\runtime\tests\AssessmentItemSessionState;

use qtism\common\datatypes\Identifier;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentTestPlace;
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
        $this->assertSame(null, $session['P01.duration']);
        $this->assertSame(null, $session['S01.duration']);
        $this->assertSame(null, $session['itemsubset.duration']);

        // Try the same on a running test session.
        // The candidate begins the test session at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT0S')));
         
        // Q01.
        // The candidate begins an attempt on Q01 at 13:00:02
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 1 second on item Q01.
        $session->setTime(new DateTime('2014-07-14T13:00:03+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT3S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT3S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT3S')));
        $session->moveNext();
         
        // Q02.
        // The candidate begins an attempt on Q02 at 13:00:04
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 3 second on the item.
        $session->setTime(new DateTime('2014-07-14T13:00:07+00:00', new DateTimeZone('UTC')));
        $session->skip();
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT7S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT7S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT7S')));
        $session->moveNext();
         
        // Try to get a duration that does not exist.
        $this->assertSame(null, $session['P02.duration']);
        
        // Brutal end...
        $session->endTestSession();
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT7S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT7S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT7S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT3S')));
    }
    
    public function testTestPartTimeLimitsLinear() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/timelimits_testparts_linear_individual.xml');
        
        // The candidate begins the test session at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
         
        // Q01.
        // The candidate begins an attempt on Q01 at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 2 seconds on the item (maxTime = 5).
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        $session->moveNext();
        
        // Check if the maximum remaining time for the test part is indeed 3 seconds (2 seconds spent on Q01).
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::TEST_PART);
        $this->assertTrue($timeConstraints[0]->getMaximumRemainingTime()->equals(new Duration('PT3S')));
         
        // Q02.
        // The candidate begins an attempt on Q02 at 13:00:02.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 2 seconds on item Q02 and skip the item.
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->skip();
        $session->moveNext();
        
        // Check if the maximum remaining time for the test part is indeed 1 second (2 seconds on Q01 + 2 seconds on Q02).
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::TEST_PART);
        $this->assertTrue($timeConstraints[0]->getMaximumRemainingTime()->equals(new Duration('PT1S')));
        
         
        // Q03.
        // The candidate begins an attempt on Q03 at 13:00:04.
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        try {
            // The candidate spends 2 seconds on the item.
            // P01.duration = 6 > maxTime -> exception !
            $session->setTime(new DateTime('2014-07-14T13:00:06+00:00', new DateTimeZone('UTC')));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new Identifier('H'), new Identifier('O')))))));
            $this->assertFalse(true);
        }
        catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::TEST_PART_DURATION_OVERFLOW, $e->getCode());
            $session->moveNext();
        }
         
        // We should have automatically because the previous call to moveNext() does
        // not allow time out items to be reached.
        $this->assertEquals('P02', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        
        // Check if P02 time constraint is there (maxtime = 1).
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::TEST_PART);
        $this->assertTrue($timeConstraints[0]->getMaximumRemainingTime()->equals(new Duration('PT1S')));
         
        // Q04.
        // The candidate begins an attempt on Q04 at 13:00:06.
        $session->setTime(new DateTime('2014-07-14T13:00:06+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
         
        try {
            // The candidate spends 2 seconds on Q04.
            $session->setTime(new DateTime('2014-07-14T13:00:08+00:00', new DateTimeZone('UTC')));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))));
            $this->assertTrue(false);
        }
        catch (AssessmentTestSessionException $e) {
            // The maxtime of 1 second ruled by P02 is reached.
            $this->assertEquals(AssessmentTestSessionException::TEST_PART_DURATION_OVERFLOW, $e->getCode());
            $session->moveNext();
        }
         
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertFalse($session->getCurrentAssessmentItemRef());
         
        // Ok with outcome processing?
        $this->assertEquals(1, $session['NRESPONSED']->getValue());
    }
    
    /**
     * This test aims at testing if it is possible to force the attempt to be performed
     * even if time constraints in force are exceeded, by the use of the $allowLateSubmission
     * argument.
     * 
     */
    public function testForceLateSubmission($forceLateSubmission = true) {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/force_late_submission.xml');
        
        // The candidate begins the test session at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        
        // The Candidate begins the attempt on Q01 at 13:00:02. The maximum time limit is 1 second.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        try {
            // The candidate ends the attempt on Q02 at 13:00:04. He spent 2 seconds (maxtime = 1) on the item.
            $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new Identifier('ChoiceA')))), $forceLateSubmission);
            $session->moveNext();
            
            // If $forceLateSubmission = true, an exception is thrown and we go the catch block.
            $this->assertTrue($forceLateSubmission, '$forceLateSubmission is false but the attempt dit not raised an exception.');
            $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q01.SCORE']);
            $this->assertEquals(1.0, $session['Q01.SCORE']->getValue());
            $this->assertFalse($session->isRunning());
            
            // What if $forceLateSubmission = false ? :oP
            if ($forceLateSubmission === true) {
                $this->testForceLateSubmission(false);
            }
        }
        catch (AssessmentTestSessionException $e) {
            $this->assertFalse($forceLateSubmission, '$forceLateSubmission is true but the attempt should have been correctly ended. ' . $e->getMessage());
            
            // We get an item duration overflow, because $forceLateSubmission = false and we spent 2 seconds (maxtime = 1) on item Q01.
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_DURATION_OVERFLOW, $e->getCode());
        }
    }
    
    /**
     * This test aims at testing that an exception is thrown if we move
     * to a next target item which is timed out.
     * 
     */
    public function testJumpToTargetTimeout($allowTimeout = false) {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/move_next_target_timeout.xml');
        
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        
        // Jump to the target item (the 2nd and last one) to outreach timings.
        $session->jumpTo(1);
        
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        
        // The Q02 item session should be in closed state because the max time is reached...
        $q02Sessions = $session->getAssessmentItemSessions('Q02');
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $q02Sessions[0]->getState());
        
        // Move back to item Q01...
        $session->moveBack();
        
        // And jump again on item Q02, which is time out.
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
    
    public function testTimeConstraints() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/remaining_time_1.xml');
        
        // The candidate begins the test session at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        
        // The candidate begins an attempt on Q01 at 13:00:02
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        $timeConstraints = $session->getTimeConstraints();
        $this->assertEquals(4, count($timeConstraints));
        
        // AssessmentTest level
        $this->assertFalse($timeConstraints[0]->getMaximumRemainingTime());
        $this->assertFalse($timeConstraints[0]->getMinimumRemainingTime());
        $this->assertFalse($timeConstraints[0]->maxTimeInForce());
        $this->assertFalse($timeConstraints[0]->minTimeInForce());
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $timeConstraints[0]->getSource());
        
        // TestPart level
        $this->assertFalse($timeConstraints[1]->getMaximumRemainingTime());
        $this->assertFalse($timeConstraints[1]->getMinimumRemainingTime());
        $this->assertFalse($timeConstraints[1]->maxTimeInForce());
        $this->assertFalse($timeConstraints[1]->minTimeInForce());
        $this->assertInstanceOf('qtism\\data\\TestPart', $timeConstraints[1]->getSource());
        
        // AssessmentSection level (1st)
        $this->assertFalse($timeConstraints[2]->getMaximumRemainingTime());
        $this->assertFalse($timeConstraints[2]->getMinimumRemainingTime());
        $this->assertFalse($timeConstraints[2]->maxTimeInForce());
        $this->assertFalse($timeConstraints[2]->minTimeInForce());
        $this->assertInstanceOf('qtism\\data\\AssessmentSection', $timeConstraints[2]->getSource());
        
        // AssessmentItem level
        $this->assertEquals('PT1S', $timeConstraints[3]->getMinimumRemainingTime()->__toString());
        $this->assertEquals('PT3S', $timeConstraints[3]->getMaximumRemainingTime()->__toString());
        $this->assertTrue($timeConstraints[3]->maxTimeInForce());
        $this->assertTrue($timeConstraints[3]->minTimeInForce());
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $timeConstraints[3]->getSource());
        
        // The candidate ends an attempt on Q01 at 13:00:04 (time elapsed on item is 2 seconds).
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::ASSESSMENT_ITEM);
        $this->assertEquals(1, count($timeConstraints));
        $this->assertEquals('PT0S', $timeConstraints[0]->getMinimumRemainingTime()->__toString());
        $this->assertEquals('PT1S', $timeConstraints[0]->getMaximumRemainingTime()->__toString());
        $this->assertTrue($timeConstraints[0]->minTimeInForce());
        $this->assertTrue($timeConstraints[0]->maxTimeInForce());
        $session->moveNext();
        
        // The candidate begins an attempt on Q02 at 13:00:05.
        $session->setTime(new DateTime('2014-07-14T13:00:05+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate ends an attempt on Q02 at 13:00:08.
        $session->setTime(new DateTime('2014-07-14T13:00:08+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
        $timeConstraints = $session->getTimeConstraints();
        $this->assertFalse($timeConstraints[3]->getMinimumRemainingTime());
        $this->assertEquals('PT0S', $timeConstraints[3]->getMaximumRemainingTime()->__toString());
        $this->assertFalse($timeConstraints[3]->minTimeInForce());
        $this->assertTrue($timeConstraints[3]->maxTimeInForce());
        
        $session->moveNext();
        $session->beginAttempt();
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::ASSESSMENT_ITEM);
        $this->assertFalse($timeConstraints[0]->getMinimumRemainingTime());
        $this->assertFalse($timeConstraints[0]->getMaximumRemainingTime());
        $this->assertTrue($timeConstraints[0]->allowLateSubmission());
        $this->assertFalse($timeConstraints[0]->minTimeInForce());
        $this->assertFalse($timeConstraints[0]->maxTimeInForce());
    }
    
    public function testTimeConstraintsConsiderMinTime() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/dont_consider_mintime.xml', false);
        
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        
        // Q01 - timeLimits on assessmentItemRef - minTime = 1, maxTime = 3
        try {
            $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
            $session->beginAttempt();
            
            $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
            
            // No exception should be thrown even if minTime = 1. Indeed, min time
            // are not to be considered.
            $this->assertTrue(true);
        }
        catch (AssessmentTestSessionException $e) {
            $this->fail("No exception should be thrown because minTime must not be considered on Q01.");
        }
        
        // On the other hand, if we go back to min time consideration...
        unset($session);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/dont_consider_mintime.xml', true);
        
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        try {
            // Minimum time not respected...
            $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
            $session->beginAttempt();
            
            $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
            $this->fail("An exception should be thrown because minTime must be considered now on Q01.");
        }
        catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_DURATION_UNDERFLOW, $e->getCode(), "The thrown exception should have code ASSESSMENT_ITEM_DURATION_UNDERFLOW, exception message is: " . $e->getMessage());
        }
    }
    
    public function testDurationBetweenItems() {
        /*
         * This test aims at testing that the duration of the whole test is not incremented while a
         * candidate is between 2 items, and then, not interacting.
         * 
         * Before reading/editing this test case, please make sure to understand that Test session related
         * timings (test, parts, sections) are disconnected from item related timings. If a candidate
         * stays between two items in the flow, without interacting, the test session related timings
         * continue to increase while the item related timings are frozen. Indeed, in this situation
         * the item session is in suspended mode.
         */
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/between_items.xml');
        
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        
        // In this situation, the duration increases.
        // Begin attempt on Q01.
        $session->setTime(new DateTime('2014-07-14T13:00:01+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        $this->assertEquals(0, $session['Q01.duration']->getSeconds(true));
        $this->assertEquals(1, $session['S01.duration']->getSeconds(true));
        $this->assertEquals(1, $session['TP01.duration']->getSeconds(true));
        $this->assertEquals(1, $session['duration']->getSeconds(true));
        
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        
        // We are now between Q01 and Q02, the duration of items must not increase
        // but the test related timing increase.
        $session->setTime(new DateTime('2014-07-14T13:00:10+00:00', new DateTimeZone('UTC')));
        $this->assertEquals(1, $session['Q01.duration']->getSeconds(true));
        $this->assertEquals(0, $session['Q02.duration']->getSeconds(true));
        $this->assertEquals(10, $session['S01.duration']->getSeconds(true));
        $this->assertEquals(10, $session['TP01.duration']->getSeconds(true));
        $this->assertEquals(10, $session['duration']->getSeconds(true));
        
        // Move to Q02 and begin attempt after 2 seconds.
        $session->moveNext();
        
        $session->setTime(new DateTime('2014-07-14T13:00:12+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate thinks for 4 seconds and ends the attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:16+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        
        $this->assertEquals(1, $session['Q01.duration']->getSeconds(true));
        $this->assertEquals(4, $session['Q02.duration']->getSeconds(true));
        $this->assertEquals(16, $session['S01.duration']->getSeconds(true));
        $this->assertEquals(16, $session['TP01.duration']->getSeconds(true));
        $this->assertEquals(16, $session['duration']->getSeconds(true));
        
        // The test session really ends 2 seconds later.
        $session->setTime(new DateTime('2014-07-14T13:00:18+00:00', new DateTimeZone('UTC')));
        $session->moveNext();
        
        $this->assertEquals(1, $session['Q01.duration']->getSeconds(true));
        $this->assertEquals(4, $session['Q02.duration']->getSeconds(true));
        $this->assertEquals(18, $session['S01.duration']->getSeconds(true));
        $this->assertEquals(18, $session['TP01.duration']->getSeconds(true));
        $this->assertEquals(18, $session['duration']->getSeconds(true));
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }
    
    public function testMultipleOccurences() {
        /*
         * This test aims at testing how duration behaves
         * when multiple occurences of the same item are involved in
         * the test.
         */
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/multiple_occurences.xml');
        
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        
        // Begin the Q01.1 item directly without waiting (same time as for beginTestSession()).
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate takes 1 second to respond.
        $session->setTime(new DateTime('2014-07-14T13:00:01+00:00', new DateTimeZone('UTC')));
        $session->skip();
        $session->moveNext();
        
        // Begin the Q01.2 item 3 seconds after the beginning of the test session.
        $session->setTime(new DateTime('2014-07-14T13:00:03+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate takes 4 seconds to respond.
        $session->setTime(new DateTime('2014-07-14T13:00:07+00:00', new DateTimeZone('UTC')));
        $session->skip();
        $session->moveNext();
        
        // Begin Q01.3 and do it lightning fast (0 seconds).
        $session->setTime(new DateTime('2014-07-14T13:00:07+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate takes 0 seconds to respond.
        $session->setTime(new DateTime('2014-07-14T13:00:07+00:00', new DateTimeZone('UTC')));
        $session->skip();
        $session->moveNext();
        
        $this->assertEquals(1, $session['Q01.1.duration']->getSeconds(true));
        $this->assertEquals(4, $session['Q01.2.duration']->getSeconds(true));
        $this->assertEquals(0, $session['Q01.3.duration']->getSeconds(true));
        $this->assertEquals(7, $session['duration']->getSeconds(true));
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }
 }