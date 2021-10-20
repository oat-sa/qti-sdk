<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionAttemptsTest
 */
class AssessmentTestSessionAttemptsTest extends QtiSmAssessmentTestSessionTestCase
{
    /** @var AssessmentTestSession */
    private $session;

    public function setUp(): void
    {
        $this->session = self::instantiate(self::samplesDir() . 'custom/runtime/attempts/max_3_attempts_nonlinear.xml');
    }

    public function testMultipleAttempts()
    {
        $this->session->beginTestSession();

        // Q01 - first attempt.
        $this->session->beginAttempt();
        $this->session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_COMPLETED, $this->session['Q01.completionStatus']);

        // Q01 - second attempt.
        $this->session->beginAttempt();
        $this->session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))]));

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_COMPLETED, $this->session['Q01.completionStatus']);

        // Q01 - third attempt. The completion status is now completed.
        $this->session->beginAttempt();
        $this->session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_COMPLETED, $this->session['Q01.completionStatus']);
    }

    public function testDoesNotTakeAnAttemptWhenInvokingBeginAttemptConsecutivelyWithoutEndingTheAttempt()
    {
        $this->session->beginTestSession();

        // Q01 - first attempt.
        $this->session->beginAttempt();

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_UNKNOWN, $this->session['Q01.completionStatus']);
        $this::assertEquals(1, $this->session['Q01.numAttempts']->getValue());

        // Q01 - same attempt.
        $this->session->beginAttempt();

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_UNKNOWN, $this->session['Q01.completionStatus']);
        $this::assertEquals(1, $this->session['Q01.numAttempts']->getValue());
    }

    public function testThrowsWhenMaxAttemptsIsReached()
    {
        $this::expectException(AssessmentTestSessionException::class);
        $this::expectExceptionMessage('Maximum number of attempts of Item Session \'Q01.0\' reached.');

        $this->session->beginTestSession();

        // Q01 - first attempt.
        $this->session->beginAttempt();
        $this->session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // Q01 - second attempt.
        $this->session->beginAttempt();
        $this->session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))]));

        // Q01 - third attempt. The completion status is now completed.
        $this->session->beginAttempt();
        $this->session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // Must throw an exception
        $this->session->beginAttempt();
    }
}
