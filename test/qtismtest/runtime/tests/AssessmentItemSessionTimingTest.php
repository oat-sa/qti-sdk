<?php

namespace qtismtest\runtime\tests;

use DateTime;
use DateTimeZone;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\ItemSessionControl;
use qtism\data\TimeLimits;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtismtest\QtiSmAssessmentItemTestCase;

/**
 * Class AssessmentItemSessionTimingTest
 */
class AssessmentItemSessionTimingTest extends QtiSmAssessmentItemTestCase
{
    public function testBeginItemSession()
    {
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The session duration must remain the PT0S, because we are not interacting yet.
        $this->assertTrue($session['duration']->equals(new QtiDuration('PT0S')));

        // The time reference must remain the same as the one provided at item session beginning time.
        $this->assertEquals('2014-07-14@13:00:00@UTC', $session->getTimeReference()->format('Y-m-d@H:i:s@T'));
    }

    /**
     * @depends testBeginItemSession
     */
    public function testBeginFirstAttempt()
    {
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 3 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:03'));
        $session->beginAttempt();

        // Remember that time-tracking is not enabled in the INITIAL state.
        $this->assertTrue($session['duration']->equals(new QtiDuration('PT0S')));
        $this->assertEquals('2014-07-14@13:00:03@UTC', $session->getTimeReference()->format('Y-m-d@H:i:s@T'));
    }

    /**
     * @depends testBeginFirstAttempt
     */
    public function testEndAttempt()
    {
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();

        // The candidate spent 15 seconds to end his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:17'));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));

        // Duration should be 15S.
        $this->assertTrue($session['duration']->equals(new QtiDuration('PT15S')));

        // Only 1 attempt, so that the session must be closed.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getState());
    }

    /**
     * @depends testBeginFirstAttempt
     */
    public function testBeginAttemptTimeOverflow()
    {
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));
        $session->setTimeLimits($timeLimits);
        $session->getItemSessionControl()->setMaxAttempts(0);

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();

        // The candidate spends 31 seconds for the attempt and begins brutally a new one...
        $session->setTime(self::createDate('2014-07-14 13:00:33'));

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage("A new attempt for item 'Q01' is not allowed. The maximum time limit in force is reached.");

        $session->beginAttempt();
    }

    /**
     * @depends testEndAttempt
     */
    public function testEndItemSession()
    {
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));

        // Give infinite attempts.
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);
        $session->setItemSessionControl($itemSessionControl);

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();

        // The candidate spent 15 seconds to end his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:17'));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));

        // Infinite number of attempts, so the session is not closed.
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getState());

        // Close the session. The duration remains the same.
        $session->endItemSession();
        $this->assertTrue($session['duration']->equals(new QtiDuration('PT15S')));
    }

    /**
     * @depends testBeginFirstAttempt
     */
    public function testEndItemSessionBrutal()
    {
        // -- We will close the session during an attempt.
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();

        // Close the session while the candidate spent 10 seconds on the attempt, but without ending it.
        $session->setTime(self::createDate('2014-07-14 13:00:12'));
        $session->endItemSession();
        $this->assertTrue($session['duration']->equals(new QtiDuration('PT10S')));
    }

    /**
     * @depends testBeginFirstAttempt
     */
    public function testGetStateTimeOverflow()
    {
        // -- We test if after a setTime that overflows maxTime,
        //    the session is indeed closed.
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));
        $session->setTimeLimits($timeLimits);

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();

        // The candidate spent 60 seconds on the attempt.
        $session->setTime(self::createDate('2014-07-14 13:01:02'));
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getState());
    }

    /**
     * @depends testBeginFirstAttempt
     */
    public function testEndAttemptTimeOverflowNoLateSubmission()
    {
        $session = $this->instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));
        $session->setTimeLimits($timeLimits);

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();

        // The candidate spent 60 seconds on the attempt.
        $session->setTime(new DateTime('2014-07-14T13:01:02+00:00', new DateTimeZone('UTC')));
        // Extra check: make sure that duration cannot be longer than maxTime.
        $this->assertTrue($session['duration']->equals(new QtiDuration('PT30S')));

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage('The maximum time to be spent on the item session has been reached.');

        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
    }

    /**
     * @depends testEndAttemptTimeOverflowNoLateSubmission
     */
    public function testEndAttemptTimeOverflowWithLateSubmission()
    {
        $session = $this->instantiateBasicAssessmentItemSession();

        $timeLimits = new TimeLimits(null, new QtiDuration('PT30S'));
        $timeLimits->setAllowLateSubmission(true);
        $session->setTimeLimits($timeLimits);

        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(self::createDate('2014-07-14 13:00:00'));
        $session->beginItemSession();

        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(self::createDate('2014-07-14 13:00:02'));
        $session->beginAttempt();

        // The candidate spent 60 seconds on the attempt.
        $session->setTime(self::createDate('2014-07-14 13:01:02'));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));

        // The attempt is taken into account because allowLateSubmission = true.
        $this->assertEquals(1, $session['numAttempts']->getValue());

        // The session is closed.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getState());
    }

    public function testEvolutionBasicTimeLimitsUnderflowOverflow()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();

        // Give more than one attempt.
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(2);
        $itemSession->setItemSessionControl($itemSessionControl);

        // No late submission allowed.
        $timeLimits = new TimeLimits(new QtiDuration('PT1S'), new QtiDuration('PT2S'));
        $itemSession->setTimeLimits($timeLimits);

        $itemSession->setTime(self::createDate('2014-07-14 13:00:00'));
        $itemSession->beginItemSession();

        // End the attempt before minTime of 1 second.
        $this->assertEquals(2, $itemSession->getRemainingAttempts());
        $itemSession->beginAttempt();
        $this->assertEquals(1, $itemSession->getRemainingAttempts());

        try {
            $itemSession->endAttempt();
            // An exception MUST be thrown.
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_UNDERFLOW, $e->getCode());
        }

        // Check that numAttempts is taken into account &
        // that the session is correctly suspended, waiting for
        // the next attempt.
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $itemSession->getState());

        // Try again by waiting too much to respect max time at endAttempt time.
        $itemSession->beginAttempt();
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $itemSession->setTime(self::createDate('2014-07-14 13:00:03'));

        try {
            $itemSession->endAttempt();
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
        }

        $this->assertEquals(2, $itemSession['numAttempts']->getValue());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
    }

    public function testEvolutionBasicMultipleAttempts()
    {
        $count = 5;
        $attempts = [new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB'), new QtiIdentifier('ChoiceC'), new QtiIdentifier('ChoiceD'), new QtiIdentifier('ChoiceE')];
        $expected = [new QtiFloat(0.0), new QtiFloat(1.0), new QtiFloat(0.0), new QtiFloat(0.0), new QtiFloat(0.0)];

        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts($count);
        $itemSession->setItemSessionControl($itemSessionControl);

        $itemSession->setTime(self::createDate('2014-07-14 13:00:00'));
        $itemSession->beginItemSession();

        for ($i = 0; $i < $count; $i++) {
            // Here, manual set up of responses.
            $this->assertTrue($itemSession->isAttemptable());
            $itemSession->beginAttempt();

            // simulate some time... 1 second to answer the item.
            $t = $i + 1;
            $itemSession->setTime(self::createDate("2014-07-14 13:00:0${t}"));

            $itemSession['RESPONSE'] = $attempts[$i];
            $itemSession->endAttempt();
            $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
            $this->assertTrue($expected[$i]->equals($itemSession['SCORE']));
            $this->assertEquals($t, $itemSession['numAttempts']->getValue());
        }

        // The total duration should have taken 5 seconds.
        $this->assertEquals(5, $itemSession['duration']->getSeconds(true));

        // one more and we get an exception... :)
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::ATTEMPTS_OVERFLOW, $e->getCode());
        }
    }

    public function testAllowLateSubmissionNonAdaptive()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();

        $timeLimits = new TimeLimits(null, new QtiDuration('PT1S'), true);
        $itemSession->setTimeLimits($timeLimits);

        $itemSession->setTime(self::createDate('2014-07-14 13:00:00'));
        $itemSession->beginItemSession();

        $itemSession->beginAttempt();
        $itemSession['RESPONSE'] = new QtiIdentifier('ChoiceB');

        // No exception because late submission is allowed.
        $itemSession->setTime(self::createDate('2014-07-14 13:00:05'));
        $itemSession->endAttempt();
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
    }

    public function testDurationBrutalSessionClosing()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();

        $itemSession->setTime(self::createDate('2014-07-14 13:00:00'));
        $itemSession->beginItemSession();
        $this->assertEquals('PT0S', $itemSession['duration']->__toString());

        $this->assertTrue($itemSession->isAttemptable());
        $itemSession->beginAttempt();

        $itemSession->setTime(self::createDate('2014-07-14 13:00:01'));

        // End session while attempting (brutal x))
        $itemSession->endItemSession();
        $this->assertEquals('PT1S', $itemSession['duration']->__toString());
    }

    public function testRemainingTimeOne()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $this->assertFalse($itemSession->getRemainingTime());
        $timeLimits = new TimeLimits();
        $timeLimits->setMaxTime(new QtiDuration('PT3S'));
        $itemSession->setTimeLimits($timeLimits);

        $itemSession->setTime(self::createDate('2014-07-14 13:00:00'));
        $itemSession->beginItemSession();
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $this->assertTrue($itemSession->getRemainingTime()->equals(new QtiDuration('PT3S')));

        $itemSession->beginAttempt();
        $itemSession->setTime(self::createDate('2014-07-14 13:00:02'));
        $this->assertTrue($itemSession->getRemainingTime()->equals(new QtiDuration('PT1S')));

        $itemSession->setTime(self::createDate('2014-07-14 13:00:03'));
        $this->assertTrue($itemSession->getRemainingTime()->equals(new QtiDuration('PT0S')));

        try {
            $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
            // Must be rejected, no more time remaining!!!
            $this->assertFalse(true);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
            $this->assertTrue($itemSession->getRemainingTime()->equals(new QtiDuration('PT0S')));
        }
    }

    public function testRemainingTimeTwo()
    {
        // by default, there is no max time limit.
        $itemSession = $this->instantiateBasicAdaptiveAssessmentItem();
        $this->assertFalse($itemSession->getRemainingTime());

        $itemSession->setTime(self::createDate('2014-07-14 13:00:02'));
        $itemSession->beginItemSession();

        $itemSession->beginAttempt();
        $this->assertFalse($itemSession->getRemainingTime());
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $this->assertEquals('incomplete', $itemSession['completionStatus']->getValue());

        $this->assertFalse($itemSession->getRemainingTime());
        $itemSession->beginAttempt();
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
        $this->assertFalse($itemSession->getRemainingTime());
    }
}
