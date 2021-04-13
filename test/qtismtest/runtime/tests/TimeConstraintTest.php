<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiDuration;
use qtism\data\AssessmentItemRef;
use qtism\data\NavigationMode;
use qtism\data\TimeLimits;
use qtism\runtime\tests\TimeConstraint;
use qtismtest\QtiSmTestCase;

/**
 * Class TimeConstraintTest
 */
class TimeConstraintTest extends QtiSmTestCase
{
    public function testInstantiation()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $timeConstraint = new TimeConstraint($assessmentItemRef, new QtiDuration('PT20S'));

        $this::assertInstanceOf(AssessmentItemRef::class, $timeConstraint->getSource());
        $this::assertInstanceOf(QtiDuration::class, $timeConstraint->getDuration());
    }

    public function testNoConstraints()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $timeConstraint = new TimeConstraint($assessmentItemRef, new QtiDuration('PT20S'));

        // No timelimits in force.
        $this::assertFalse($timeConstraint->getMaximumRemainingTime());
        $this::assertFalse($timeConstraint->getMinimumRemainingTime());
        $this::assertFalse($timeConstraint->minTimeInForce());
        $this::assertFalse($timeConstraint->maxTimeInForce());
    }

    public function testNegativeTime()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $timeLimits = new TimeLimits(null, new QtiDuration('PT10S'));
        $assessmentItemRef->setTimeLimits($timeLimits);
        $timeConstraint = new TimeConstraint($assessmentItemRef, new QtiDuration('PT20S'));

        $maxRemaining = $timeConstraint->getMaximumRemainingTime();
        $this::assertEquals('PT0S', $maxRemaining->__toString());

        $minRemaining = $timeConstraint->getMinimumRemainingTime();
        $this::assertFalse($minRemaining);
    }

    public function testDoesAllowLateSubmission()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $timeLimits = new TimeLimits(null, new QtiDuration('PT10S'), true);
        $assessmentItemRef->setTimeLimits($timeLimits);
        $timeConstraint = new TimeConstraint($assessmentItemRef, new QtiDuration('PT5S'));
        $this::assertTrue($timeConstraint->allowLateSubmission());

        $timeLimits->setAllowLateSubmission(false);
        $this::assertFalse($timeConstraint->allowLateSubmission());

        $timeLimits->setMaxTime(null);
        $this::assertTrue($timeConstraint->allowLateSubmission());

        $assessmentItemRef->setTimeLimits(null);
        $this::assertTrue($timeConstraint->allowLateSubmission());
    }

    public function testRemainingTime()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $timeConstraint = new TimeConstraint($assessmentItemRef, new QtiDuration('PT20S'));
        $this::assertFalse($timeConstraint->maxTimeInForce());
        $this::assertFalse($timeConstraint->minTimeInForce());
        // There's no max remaining time nor min remaining time.
        $this::assertFalse($timeConstraint->getMaximumRemainingTime());
        $this::assertFalse($timeConstraint->getMinimumRemainingTime());

        $timeLimits = new TimeLimits();
        $assessmentItemRef->setTimeLimits($timeLimits);
        // There's still no max nor min remaining time.
        $this::assertFalse($timeConstraint->getMaximumRemainingTime());
        $this::assertFalse($timeConstraint->getMinimumRemainingTime());

        $timeLimits->setMinTime(new QtiDuration('PT30S'));
        $this::assertEquals('PT10S', $timeConstraint->getMinimumRemainingTime()->__toString());
        $this::assertFalse($timeConstraint->getMaximumRemainingTime());

        $timeLimits->setMaxTime(new QtiDuration('PT50S'));
        $this::assertEquals('PT30S', $timeConstraint->getMaximumRemainingTime()->__toString());
    }

    public function testNonLinearNavigationMode()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $timeLimits = new TimeLimits(new QtiDuration('PT1S'), new QtiDuration('PT2S'), false);
        $assessmentItemRef->setTimeLimits($timeLimits);
        $timeConstraint = new TimeConstraint($assessmentItemRef, new QtiDuration('PT1S'), NavigationMode::NONLINEAR);

        // Minimum times are applicable to assessmentSections and assessmentItems only when linear navigation
        // mode is in effect.
        $this::assertFalse($timeConstraint->minTimeInForce());
        $this::assertFalse($timeConstraint->getMinimumRemainingTime());
    }

    public function testLinearNavigationMode()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $timeLimits = new TimeLimits(new QtiDuration('PT1S'), new QtiDuration('PT2S'), false);
        $assessmentItemRef->setTimeLimits($timeLimits);
        $timeConstraint = new TimeConstraint($assessmentItemRef, new QtiDuration('PT1S'), NavigationMode::LINEAR);

        // Minimum times are applicable to assessmentSections and assessmentItems only when linear navigation
        // mode is in effect, this is the case!
        $this::assertTrue($timeConstraint->minTimeInForce());
        $this::assertEquals('PT0S', $timeConstraint->getMinimumRemainingTime()->__toString());
    }
}
