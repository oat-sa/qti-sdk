<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSession;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionAttemptsTest
 */
class AssessmentTestSessionAttemptsTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testMultipleAttempts()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/attempts/max_3_attempts_nonlinear.xml');
        $session->beginTestSession();

        // Q01 - first attempt.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_COMPLETED, $session['Q01.completionStatus']);

        // Q02 - second attempt.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))]));

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_COMPLETED, $session['Q01.completionStatus']);

        // Q03 - third attempt. The completion status is now completed.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $this::assertEquals(AssessmentItemSession::COMPLETION_STATUS_COMPLETED, $session['Q01.completionStatus']);
    }
}
