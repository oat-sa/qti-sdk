<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionExitTest
 */
class AssessmentTestSessionExitTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testExitSection()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exitsection.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we should EXIT_SECTION. We should
        // then be redirected to S02.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should arrive at section 2.
        $testSession->moveNext();
        $this::assertEquals('S02', $testSession->getCurrentAssessmentSection()->getIdentifier());
        $this::assertTrue($testSession->isRunning());
    }

    public function testExitSectionEndOfTest()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exitsectionendoftest.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we will EXIT_SECTION. We should
        // be then redirected to the end of the test, because S01 is the unique section
        // of the test.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should be at the end of the test.
        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());

        // All session closed (parano mode)?
        $itemSessions = $testSession->getAssessmentItemSessions('Q01');
        $q01Session = $itemSessions[0];
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $q01Session->getState());

        // For Q02, we should not get any result because we by-passed it with the branchRule.
        $itemSessions = $testSession->getAssessmentItemSessions('Q02');
        $this::assertFalse($itemSessions);
        $this::assertFalse($testSession->isRunning());
    }

    public function testExitSectionFromEndOfSection()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exitsectionfromendofsection.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we will EXIT_SECTION. We should
        // be then redirected to next S02 section (Q02).
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should be at section S02, item Q02.
        $testSession->moveNext();

        $this::assertEquals('Q02', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->moveNext();
        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
        $this::assertFalse($testSession->isRunning());
    }

    public function testExitSectionPreconditionsEndOfTest()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exitsectionpreconditions.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we will EXIT_SECTION. We should
        // be then redirected to the end of the test, because Q03 has a precondition
        // which is never satisfied (return always false).
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should be at the end of the test.
        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());

        // All session closed (parano mode again)?
        $itemSessions = $testSession->getAssessmentItemSessions('Q01');
        $q01Session = $itemSessions[0];
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $q01Session->getState());

        // For Q02 and Q03, we should not get any result because we both by-passed them with the initial branchRule
        // and the ending preCondition.
        $itemSessions = $testSession->getAssessmentItemSessions('Q02');
        $this::assertFalse($itemSessions);

        $itemSessions = $testSession->getAssessmentItemSessions('Q03');
        $this::assertFalse($itemSessions);
        $this::assertFalse($testSession->isRunning());
    }

    public function testExitTestPart()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exittestpart.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we should EXIT_TESTPART. We should
        // then be redirected to P02.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should arrive at testPart 2
        $testSession->moveNext();
        $this::assertEquals('P02', $testSession->getCurrentTestPart()->getIdentifier());
        $this::assertTrue($testSession->isRunning());
        $testSession->moveNext();
        $this::assertFalse($testSession->isRunning());
    }

    public function testExitTestPartEndOfTest()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exittestpartendoftest.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we will EXIT_TESTPART. We should
        // be then redirected to the end of the test, because T01 is the unique testPart
        // of the test.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should be at the end of the test.
        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());

        // All session closed (parano mode all over again)?
        $itemSessions = $testSession->getAssessmentItemSessions('Q01');
        $q01Session = $itemSessions[0];
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $q01Session->getState());

        // For Q02, we should not get any result because we by-passed it with the branchRule.
        $itemSessions = $testSession->getAssessmentItemSessions('Q02');
        $this::assertFalse($itemSessions);
        $this::assertFalse($testSession->isRunning());
    }

    public function testExitTestPartPreconditionsEndOfTest()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exittestpartpreconditions.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we will EXIT_TESTPART. We should
        // be then redirected to the end of the test, because Q03 has a precondition
        // which is never satisfied (return always false).
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should be at the end of the test.
        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());

        // All session closed (parano mode again)?
        $itemSessions = $testSession->getAssessmentItemSessions('Q01');
        $q01Session = $itemSessions[0];
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $q01Session->getState());

        // For Q02 & Q03, we should not get any result because we both by-passed them with the initial branchRule
        // and the ending preCondition.
        $itemSessions = $testSession->getAssessmentItemSessions('Q02');
        $this::assertFalse($itemSessions);

        $itemSessions = $testSession->getAssessmentItemSessions('Q03');
        $this::assertFalse($itemSessions);
        $this::assertFalse($testSession->isRunning());
    }

    public function testExitTest()
    {
        $url = self::samplesDir() . 'custom/runtime/exits/exittest.xml';
        $testSession = self::instantiate($url);

        $testSession->beginTestSession();

        // If we get correct to the first question, we should EXIT_TEST. We should
        // then be redirected to end of the test.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // We should arrive at section 2.
        $testSession->moveNext();
        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
        $this::assertFalse($testSession->isRunning());
    }
}
