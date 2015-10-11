<?php
namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentItemSession;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentTestPlace;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\common\datatypes\QtiPoint;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\QtiDuration;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionFactory;
use qtism\data\storage\xml\XmlCompactDocument;

class AssessmentTestSessionTimingTest extends QtiSmAssessmentTestSessionTestCase {
    
    
    public function testTestPartAssessmentSectionsDurations() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        // Try to get a duration on a non-begun test session.
        $this->assertSame(null, $session['P01.duration']);
        $this->assertSame(null, $session['S01.duration']);
        $this->assertSame(null, $session['itemsubset.duration']);

        // Try the same on a running test session.
        // The candidate begins the test session at 13:00:00.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginTestSession();
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT0S')));
         
        // Q01.
        // The candidate begins an attempt on Q01 at 13:00:02
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();
        
        // The candidate spends 1 second on item Q01.
        $session->setTime(self::createDate('2014-07-14 13:00:03'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT3S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT3S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT3S')));
        $session->moveNext();
         
        // Q02.
        // The candidate begins an attempt on Q02 at 13:00:04
        $session->setTime(self::createDate('2014-07-14 13:00:04'));
        $session->beginAttempt();
        
        // The candidate spends 3 second on the item.
        $session->setTime(self::createDate('2014-07-14 13:00:07'));
        $session->skip();
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT7S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT7S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT7S')));
        $session->moveNext();
         
        // Try to get a duration that does not exist.
        $this->assertSame(null, $session['P02.duration']);
        
        // Brutal end...
        $session->endTestSession();
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT7S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT7S')));
        $this->assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT7S')));
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new QtiDuration('PT3S')));
    }
    
    public function testTestPartTimeLimitsLinear() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/timelimits_testparts_linear_individual.xml');
        
        // The candidate begins the test session at 13:00:00.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginTestSession();
         
        // Q01.
        // The candidate begins an attempt on Q01 at 13:00:00.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginAttempt();
        
        // The candidate spends 2 seconds on the item (maxTime = 5).
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))));
        $session->moveNext();
        
        // Check if the maximum remaining time for the test part is indeed 3 seconds (2 seconds spent on Q01).
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::TEST_PART);
        $this->assertTrue($timeConstraints[0]->getMaximumRemainingTime()->equals(new QtiDuration('PT3S')));
         
        // Q02.
        // The candidate begins an attempt on Q02 at 13:00:02.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();
        
        // The candidate spends 2 seconds on item Q02 and skip the item.
        $session->setTime(self::createDate('2014-07-14 13:00:04'));
        $session->skip();
        $session->moveNext();
        
        // Check if the maximum remaining time for the test part is indeed 1 second (2 seconds on Q01 + 2 seconds on Q02).
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::TEST_PART);
        $this->assertTrue($timeConstraints[0]->getMaximumRemainingTime()->equals(new QtiDuration('PT1S')));
        
         
        // Q03.
        // The candidate begins an attempt on Q03 at 13:00:04.
        $session->setTime(self::createDate('2014-07-14 13:00:04'));
        $session->beginAttempt();
        
        try {
            // The candidate spends 2 seconds on the item.
            // P01.duration = 6 > maxTime -> exception !
            $session->setTime(self::createDate('2014-07-14 13:00:06'));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('H'), new QtiIdentifier('O')))))));
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
        $this->assertTrue($timeConstraints[0]->getMaximumRemainingTime()->equals(new QtiDuration('PT1S')));
         
        // Q04.1
        // The candidate begins an attempt on Q04 at 13:00:06.
        $session->setTime(self::createDate('2014-07-14 13:00:06'));
        $session->beginAttempt();
         
        try {
            // The candidate spends 2 seconds on Q04.
            $session->setTime(self::createDate('2014-07-14 13:00:08'));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113)))));
            $this->assertTrue(false);
        }
        catch (AssessmentTestSessionException $e) {
            // The maxtime of 1 second ruled by P02 is reached.
            $this->assertEquals(AssessmentTestSessionException::TEST_PART_DURATION_OVERFLOW, $e->getCode());
            
            // Reach the end... Pass through Q04.2 and Q04.3
            $session->moveNext();
            $session->moveNext();
            $session->moveNext();
        }
         
        // Q04.2
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
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginTestSession();
        
        // The Candidate begins the attempt on Q01 at 13:00:02. The maximum time limit is 1 second.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();
        
        try {
            // The candidate ends the attempt on Q02 at 13:00:04. He spent 2 seconds (maxtime = 1) on the item.
            $session->setTime(self::createDate('2014-07-14 13:00:04'));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new QtiIdentifier('ChoiceA')))), $forceLateSubmission);
            $session->moveNext();
            
            // If $forceLateSubmission = true, an exception is thrown and we go the catch block.
            $this->assertTrue($forceLateSubmission, '$forceLateSubmission is false but the attempt dit not raised an exception.');
            $this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $session['Q01.SCORE']);
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
    
    public function testTimeConstraints() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/remaining_time_1.xml');
        
        // The candidate begins the test session at 13:00:00.
        $session->setTime(self::createDate('2014-07-14 13:00:00', 'Europe/Luxembourg'));
        $session->beginTestSession();
        
        // The candidate begins an attempt on Q01 at 13:00:02
        $session->setTime(self::createDate('2014-07-14 13:00:02', 'Europe/Luxembourg'));
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
        $session->setTime(self::createDate('2014-07-14 13:00:04', 'Europe/Luxembourg'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))));
        
        $timeConstraints = $session->getTimeConstraints(AssessmentTestPlace::ASSESSMENT_ITEM);
        $this->assertEquals(1, count($timeConstraints));
        $this->assertEquals('PT0S', $timeConstraints[0]->getMinimumRemainingTime()->__toString());
        $this->assertEquals('PT1S', $timeConstraints[0]->getMaximumRemainingTime()->__toString());
        $this->assertTrue($timeConstraints[0]->minTimeInForce());
        $this->assertTrue($timeConstraints[0]->maxTimeInForce());
        $session->moveNext();
        
        // The candidate begins an attempt on Q02 at 13:00:05.
        $session->setTime(self::createDate('2014-07-14 13:00:05', 'Europe/Luxembourg'));
        $session->beginAttempt();
        
        // The candidate ends an attempt on Q02 at 13:00:08.
        $session->setTime(self::createDate('2014-07-14 13:00:08', 'Europe/Luxembourg'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
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
    
    public function testTimeConstraintsTwo() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/dont_consider_mintime.xml');
        
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginTestSession();
        try {
            // Minimum time not respected...
            $session->setTime(self::createDate('2014-07-14 13:00:00'));
            $session->beginAttempt();
            
            $session->setTime(self::createDate('2014-07-14 13:00:00'));
            $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
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
        
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginTestSession();
        
        // In this situation, the duration increases.
        // Begin attempt on Q01.
        $session->setTime(self::createDate('2014-07-14 13:00:01'));
        $session->beginAttempt();
        
        $this->assertEquals(0, $session['Q01.duration']->getSeconds(true));
        $this->assertEquals(1, $session['S01.duration']->getSeconds(true));
        $this->assertEquals(1, $session['TP01.duration']->getSeconds(true));
        $this->assertEquals(1, $session['duration']->getSeconds(true));
        
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))));
        
        // We are now between Q01 and Q02, the duration of items must not increase
        // but the test related timing increase.
        $session->setTime(self::createDate('2014-07-14 13:00:10'));
        $this->assertEquals(1, $session['Q01.duration']->getSeconds(true));
        $this->assertEquals(0, $session['Q02.duration']->getSeconds(true));
        $this->assertEquals(10, $session['S01.duration']->getSeconds(true));
        $this->assertEquals(10, $session['TP01.duration']->getSeconds(true));
        $this->assertEquals(10, $session['duration']->getSeconds(true));
        
        // Move to Q02 and begin attempt after 2 seconds.
        $session->moveNext();
        
        $session->setTime(self::createDate('2014-07-14 13:00:12'));
        $session->beginAttempt();
        
        // The candidate thinks for 4 seconds and ends the attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:16'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))));
        
        $this->assertEquals(1, $session['Q01.duration']->getSeconds(true));
        $this->assertEquals(4, $session['Q02.duration']->getSeconds(true));
        $this->assertEquals(16, $session['S01.duration']->getSeconds(true));
        $this->assertEquals(16, $session['TP01.duration']->getSeconds(true));
        $this->assertEquals(16, $session['duration']->getSeconds(true));
        
        // The test session really ends 2 seconds later.
        $session->setTime(self::createDate('2014-07-14 13:00:18'));
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
        
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginTestSession();
        
        // Begin the Q01.1 item directly without waiting (same time as for beginTestSession()).
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginAttempt();
        
        // The candidate takes 1 second to respond.
        $session->setTime(self::createDate('2014-07-14 13:00:01'));
        $session->skip();
        $session->moveNext();
        
        // Begin the Q01.2 item 3 seconds after the beginning of the test session.
        $session->setTime(self::createDate('2014-07-14 13:00:03'));
        $session->beginAttempt();
        
        // The candidate takes 4 seconds to respond.
        $session->setTime(self::createDate('2014-07-14 13:00:07'));
        $session->skip();
        $session->moveNext();
        
        // Begin Q01.3 and do it lightning fast (0 seconds).
        $session->setTime(self::createDate('2014-07-14 13:00:07'));
        $session->beginAttempt();
        
        // The candidate takes 0 seconds to respond.
        $session->setTime(self::createDate('2014-07-14 13:00:07'));
        $session->skip();
        $session->moveNext();
        
        $this->assertEquals(1, $session['Q01.1.duration']->getSeconds(true));
        $this->assertEquals(4, $session['Q01.2.duration']->getSeconds(true));
        $this->assertEquals(0, $session['Q01.3.duration']->getSeconds(true));
        $this->assertEquals(7, $session['duration']->getSeconds(true));
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }
    
    public function testIsTimeout1() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/istimeout_1.xml');
         
        // If the session has not begun, the method systematically returns false.
        $this->assertFalse($session->isTimeout());
         
        // If no time limits in force, the test session is never considered timeout while running.
        $session->setTime(self::createDate('2015-02-02 11:54:00'));
        $session->beginTestSession();
        $this->assertSame(0, $session->isTimeout());
         
        // -- Q01 (1st attempt)
        $session->beginAttempt();
        $this->assertSame(0, $session->isTimeout());
        
        // Spend 25 seconds...
        $session->setTime(self::createDate('2015-02-02 11:54:25'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))));
        $this->assertSame(0, $session->isTimeout());
        
        // -- Q01 (2nd attempt)
        $session->beginAttempt();
        $this->assertSame(0, $session->isTimeout());
        
        // Spend 10 seconds...
        $session->setTime(self::createDate('2015-02-02 11:54:35'));
        $this->assertSame(AssessmentTestPlace::ASSESSMENT_ITEM, $session->isTimeout());
        
        // Check max time reached for item... (item maxTime = 30S)
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT30S')));
        
        // Check current time for the overall session...
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT35S')));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT35S')));
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT35S')));
        
        // Spend 20 seconds here...
        $session->setTime(self::createDate('2015-02-02 11:54:55'));
        $this->assertSame(AssessmentTestPlace::ASSESSMENT_SECTION, $session->isTimeout());
        
        // Check current time for the overall session... (session maxTime = 45S)
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT45S')));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT55S')));
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT55S')));
        
        // Spend 10 more seconds here to outreach P01's maxTime.
        $session->setTime(self::createDate('2015-02-02 11:55:05'));
        $this->assertSame(AssessmentTestPlace::TEST_PART, $session->isTimeout());
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT1M')));
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT1M5S')));
        
        // Spend 30 more seconds here to outreach istimeout's maxTime. The test session must close.
        $session->setTime(self::createDate('2015-02-02 11:55:35'));
        $this->assertSame(AssessmentTestSessionState::CLOSED, $session->getState());
        
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT45S')));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT1M')));
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT1M30S')));
    }
    
    public function testItemSessionsAreClosed1() {
        // - istimeout maxTime = 120
        // -- P01 maxTime = 90
        // --- S01 maxTime = 60
        // ---- S01A maxTime = 30
        // ----- Q01 maxTime = 10
        // ----- Q02 maxTime = 10
        // ---- S01B maxTime = 30
        // ----- Q03 maxTime = 10
        // ----- Q04 maxTime = 10
        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/istimeout_2.xml');
        
        $session->setTime(self::createDate('2015-02-02 14:25:00'));
        $session->beginTestSession();
        
        // Spend 10 seconds before the first attempt on Q01...
        $session->setTime(self::createDate('2015-02-02 14:25:10'));
        
        // -- Q01 attempt.
        $session->beginAttempt();
        // Spend 8 seconds ...
        $session->setTime(self::createDate('2015-02-02 14:25:18'));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))));
        $session->moveNext();
        
        // -- Q02 attempt.
        // Spend 15 seconds to reach the maxTime of the parent section.
        // As a result, Q01 and Q02 item sessions must be closed!
        $session->beginAttempt();
        $session->setTime(self::createDate('2015-02-02 14:25:33'));
        
        $q01Sessions = $session->getAssessmentItemSessions('Q01');
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $q01Sessions[0]->getState());
        
        $q02Sessions = $session->getAssessmentItemSessions('Q02');
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $q02Sessions[0]->getState());
        
        $session->moveNext();
        
        // Let's check durations...
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT33S')));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT33S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT33S')));
        $this->assertTrue($session['S01A.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['S01B.duration']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT8S')));
        $this->assertTrue($session['Q02.duration']->equals(new QtiDuration('PT10S')));
        
        // -- Let's dive into S01B and make it timeout.
        $q03Sessions = $session->getAssessmentItemSessions('Q03');
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $q03Sessions[0]->getState());
        
        $q04Sessions = $session->getAssessmentItemSessions('Q04');
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $q04Sessions[0]->getState());
        
        // Spend 29 seconds in S01B to make S01 timeout.
        $session->setTime(self::createDate('2015-02-02 14:26:02'));
        
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $q03Sessions[0]->getState());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $q04Sessions[0]->getState());
        
        // Let's check durations...
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT1M2S')));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT1M2S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT1M')));
        $this->assertTrue($session['S01A.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['S01B.duration']->equals(new QtiDuration('PT29S')));
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT8S')));
        $this->assertTrue($session['Q02.duration']->equals(new QtiDuration('PT10S')));
        $this->assertTrue($session['Q03.duration']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($session['Q04.duration']->equals(new QtiDuration('PT0S')));
        
        // Even if all item sessions are closed, we consider the test is still running
        // We can imagine a test taker navigating through the item flow searching
        // for a non-timed out item to take. It's however a weird situation that
        // a test driver built on top of qtism should consider.
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
        
        // Let's spend another 30 seconds to make the test part time out.
        $session->setTime(self::createDate('2015-02-02 14:26:32'));
        
        // Let's check durations...
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT1M32S')));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT1M30S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT1M')));
        $this->assertTrue($session['S01A.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['S01B.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT8S')));
        $this->assertTrue($session['Q02.duration']->equals(new QtiDuration('PT10S')));
        $this->assertTrue($session['Q03.duration']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($session['Q04.duration']->equals(new QtiDuration('PT0S')));
        
        // Let's spend another 28 seconds to reach the end of the test's maxTime.
        $session->setTime(self::createDate('2015-02-02 14:27:00'));
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        
        // Let's check durations...
        $this->assertTrue($session['istimeout.duration']->equals(new QtiDuration('PT2M')));
        $this->assertTrue($session['P01.duration']->equals(new QtiDuration('PT1M30S')));
        $this->assertTrue($session['S01.duration']->equals(new QtiDuration('PT1M')));
        $this->assertTrue($session['S01A.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['S01B.duration']->equals(new QtiDuration('PT30S')));
        $this->assertTrue($session['Q01.duration']->equals(new QtiDuration('PT8S')));
        $this->assertTrue($session['Q02.duration']->equals(new QtiDuration('PT10S')));
        $this->assertTrue($session['Q03.duration']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($session['Q04.duration']->equals(new QtiDuration('PT0S')));
        
        // All sessions still closed?
        foreach ($session->getAssessmentItemSessionStore()->getAllAssessmentItemSessions() as $itemSession) {
            $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        }
    }
    
    public function testLastItemTimeout() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/last_item_timeout.xml');
        $session->beginTestSession();
        
        $session->beginAttempt();
        sleep(2);
        $session->moveNext();
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }
    
    public function testLastItemSectionTimeout() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/last_item_section_timeout.xml');
        $session->beginTestSession();
        
        $session->beginAttempt();
        sleep(2);
        $session->moveNext();
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }
    
    public function testLastItemTestPartTimeout() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/timings/last_item_testpart_timeout.xml');
        $session->beginTestSession();
        
        $session->beginAttempt();
        sleep(2);
        $session->moveNext();
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }
 }
 