<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\ItemSessionControl;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\SubmissionMode;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmAssessmentItemTestCase;

/**
 * Class AssessmentItemSessionTest
 */
class AssessmentItemSessionTest extends QtiSmAssessmentItemTestCase
{
    public function testExternalScored()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_2/essay.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager(new FileSystemFileManager()));
        $itemSessionControl = $itemSession->getItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);
        $itemSession->beginItemSession();

        $response = new ResponseVariable(
            'RESPONSE',
            Cardinality::SINGLE,
            BaseType::STRING,
            new QtiString('some string')
        );

        $itemSession->beginAttempt();
        $responses = new State([$response]);
        $itemSession->endAttempt($responses);

        $this->assertEquals(1, $itemSession->getState());
        $this->assertTrue($itemSession->isResponded());
    }

    public function testInstantiation()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $this->assertFalse($itemSession->isNavigationNonLinear());

        // isPresented? isCorrect? isResponded? isSelected?
        $this->assertFalse($itemSession->isPresented());
        $this->assertFalse($itemSession->isCorrect());
        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));
        $this->assertTrue($itemSession->isSelected());

        $itemSession->beginItemSession();
        // After beginItemSession...
        // isPresented? isCorrect? isResponded? isSelected?
        $this->assertFalse($itemSession->isPresented());
        $this->assertFalse($itemSession->isCorrect());
        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));
        $this->assertTrue($itemSession->isSelected());
        $this->assertTrue($itemSession->isAttemptable());

        // No timelimits by default.
        $this->assertFalse($itemSession->hasTimeLimits());

        // Response variables instantiated and set to NULL?
        $this->assertInstanceOf(ResponseVariable::class, $itemSession->getVariable('RESPONSE'));
        $this->assertSame(null, $itemSession['RESPONSE']);

        // Outcome variables instantiated and set to their default if any?
        $this->assertInstanceOf(OutcomeVariable::class, $itemSession->getVariable('SCORE'));
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());

        // Built-in variables instantiated and values initialized correctly?
        $this->assertInstanceOf(ResponseVariable::class, $itemSession->getVariable('numAttempts'));
        $this->assertInstanceOf(QtiInteger::class, $itemSession['numAttempts']);
        $this->assertEquals(0, $itemSession['numAttempts']->getValue());

        $this->assertInstanceOf(ResponseVariable::class, $itemSession->getVariable('duration'));
        $this->assertInstanceOf(QtiDuration::class, $itemSession['duration']);
        $this->assertEquals('PT0S', $itemSession['duration']->__toString());

        $this->assertInstanceOf(OutcomeVariable::class, $itemSession->getVariable('completionStatus'));
        $this->assertInstanceOf(QtiString::class, $itemSession['completionStatus']);
        $this->assertEquals('not_attempted', $itemSession['completionStatus']->getValue());
        $this->assertEquals(BaseType::IDENTIFIER, $itemSession->getVariable('completionStatus')->getBaseType());

        // State is correct?
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $itemSession->getState());

        // Remaining attempts correct?
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $this->assertTrue($itemSession->isAttemptable());
    }

    public function testEvolutionBasic()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();
        $this->assertTrue($itemSession->isSelected());

        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $this->assertTrue($itemSession->isAttemptable());
        $itemSession->beginAttempt();
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertTrue($itemSession->isPresented());
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        // when the first attempt occurs, the response variable must get their default value.
        // in our case, no default value. The RESPONSE variable must remain NULL.
        $this->assertSame(null, $itemSession['RESPONSE']);
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());

        // Now, we end the attempt by providing a set of responses for the attempt. Response
        // processing will take place.

        // Note: here we provide a State object for the responses, but the value of the 'RESPONSE'
        // variable can also be set manually on the item session prior calling endAttempt(). This
        // is a matter of choice.
        $resp = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'));
        $itemSession->endAttempt(new State([$resp]));
        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));

        // The ItemSessionControl for this session was not specified, it is then
        // the default one, with default values. Because maxAttempts is not specified,
        // it is considered to be 1, because the item is non-adaptive.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertTrue($itemSession->isCorrect());

        // If we now try to begin a new attempt, we get an exception.
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();

            // An exception MUST be thrown.
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::ATTEMPTS_OVERFLOW, $e->getCode());
        }
    }

    public function testGetResponseVariables()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();

        // Get response variables with built-in ones.
        $responses = $itemSession->getResponseVariables();
        $this->assertEquals(3, count($responses));
        $this->assertTrue(isset($responses['RESPONSE']));
        $this->assertTrue(isset($responses['numAttempts']));
        $this->assertTrue(isset($responses['duration']));

        // Get response variables but ommit built-in ones.
        $responses = $itemSession->getResponseVariables(false);
        $this->assertEquals(1, count($responses));
        $this->assertTrue(isset($responses['RESPONSE']));
    }

    public function testGetOutcomeVariables()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();

        // Get outcome variables with the built-in ones included.
        $outcomes = $itemSession->getOutcomeVariables();
        $this->assertEquals(2, count($outcomes));
        $this->assertTrue(isset($outcomes['SCORE']));
        $this->assertTrue(isset($outcomes['completionStatus']));

        // Get outcome variables without the built-in 'completionStatus'.
        $outcomes = $itemSession->getOutcomeVariables(false);
        $this->assertEquals(1, count($outcomes));
        $this->assertTrue(isset($outcomes['SCORE']));
    }

    public function testEvolutionAdaptiveItem()
    {
        $itemSession = $this->instantiateBasicAdaptiveAssessmentItem();
        $itemSession->beginItemSession();

        // reminder, the value of maxAttempts is ignored when dealing with
        // adaptive items.

        // First candidate session, just give an incorrect response.
        // We do not known how much attempts to complete.
        $this->assertTrue($itemSession->isAttemptable());
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        $itemSession->beginAttempt();
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceE'))]));
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());

        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertEquals('incomplete', $itemSession['completionStatus']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());

        $itemSession->beginAttempt();
        // Second attempt, give the correct answer to be allowed to go to the next item.
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());

        // If you now try to attempt again, exception because already completed.

        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            // The session is closed, you cannot begin another attempt.
            $this->assertEquals(AssessmentItemSessionException::ATTEMPTS_OVERFLOW, $e->getCode());
        }
    }

    public function testSkippingForbiddenSimple()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setAllowSkipping(false);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();

        $itemSession->beginAttempt();

        // Test with empty state...
        try {
            $itemSession->endAttempt(new State());
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals("Skipping item 'Q01' is not allowed.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionException::SKIPPING_FORBIDDEN, $e->getCode());
        }

        // Test with null value for RESPONSE...
        try {
            $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER)]));
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals("Skipping item 'Q01' is not allowed.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionException::SKIPPING_FORBIDDEN, $e->getCode());
        }
    }

    public function testSkippingForbiddenDefaultValue()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/skipping/default_value.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setAllowSkipping(false);
        $itemSessionControl->setMaxAttempts(0);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();

        // Test with empty state...
        try {
            $itemSession->endAttempt(new State());
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals("Skipping item 'default_value' is not allowed.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionException::SKIPPING_FORBIDDEN, $e->getCode());
            $this->assertEquals('ChoiceA', $itemSession['RESPONSE']->getValue());
        }

        // Test with a value equal to default value...
        try {
            $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
            $this->assertTrue(false);
        } catch (AssessmentItemSessionException $e) {
            $this->assertEquals("Skipping item 'default_value' is not allowed.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionException::SKIPPING_FORBIDDEN, $e->getCode());
            $this->assertEquals('ChoiceA', $itemSession['RESPONSE']->getValue());
        }

        // Finally, with a value different than default value, we can end the attempt.
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $this->assertEquals('ChoiceB', $itemSession['RESPONSE']->getValue());

        // Finally bis, by allowing skipping, we can skip.
        $itemSessionControl->setAllowSkipping(true);
        $itemSession->beginAttempt();
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER)]));
        $this->assertNull($itemSession['RESPONSE']);
    }

    public function testSkippingAllowedSimple()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();

        $itemSession->beginAttempt();
        $itemSession->endAttempt(new State());

        $this->assertEquals($itemSession->getState(), AssessmentItemSessionState::CLOSED);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
        $this->assertEquals(null, $itemSession['RESPONSE']);
    }

    public function testValidResponsesInForceValid()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setValidateResponses(false);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();

        $itemSession->beginAttempt();
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD')));
        $itemSession->endAttempt($responses);
    }

    public function testIsCorrect()
    {
        $itemSession = $this->instantiateBasicAdaptiveAssessmentItem();
        $this->assertEquals(AssessmentItemSessionState::NOT_SELECTED, $itemSession->getState());

        // The item session is in NOT_SELECTED mode, then false is returned directly.
        $this->assertFalse($itemSession->isCorrect());

        $itemSession->beginItemSession();
        $itemSession->beginAttempt();

        // No response given, false is returned.
        $this->assertFalse($itemSession->isCorrect());

        $state = new State();
        $state->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')));
        $itemSession->endAttempt($state);

        // Wrong answer ('ChoiceB' is the correct one), the session is not correct.
        $this->assertEquals('incomplete', $itemSession['completionStatus']->getValue());
        $this->assertFalse($itemSession->isCorrect());

        $state['RESPONSE'] = new QtiIdentifier('ChoiceB');
        $itemSession->beginAttempt();
        $itemSession->endAttempt($state);

        // Correct answer, the session is correct!
        $this->assertTrue($itemSession->isCorrect());
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
    }

    public function testStandaloneItemSession()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/hotspot.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('A'))]);
        $itemSession->endAttempt($responses);
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());
    }

    public function testStandaloneMultipleInteractions()
    {
        $doc = new XmlDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/items/multiple_interactions.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());

        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('Choice_3'))]);
        $itemSession->endAttempt($responses);
        $this->assertEquals(6.0, $itemSession['SCORE']->getValue());
    }

    public function testModalFeedback()
    {
        $doc = new XmlDocument('2.1.0');
        $doc->load(self::samplesDir() . 'ims/items/2_1/modalFeedback.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setShowFeedback(true);
        $itemSessionControl->setMaxAttempts(0);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();

        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('true'))]);
        $itemSession->beginAttempt();
        $itemSession->endAttempt($responses);

        $this->assertEquals('correct', $itemSession['FEEDBACK']->getValue());
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $itemSession->getState());

        // new attempt!
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $itemSession->beginAttempt();
        $itemSession->endAttempt($responses);

        $this->assertEquals('incorrect', $itemSession['FEEDBACK']->getValue());
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $itemSession->getState());

        $itemSession->endItemSession();
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
    }

    public function testTemplateVariableDefault()
    {
        // This test aims at testing whether template variables
        // are correctly instantiated as part of the item session and
        // they can be used in response processing.
        $doc = new XmlDocument('2.1.0');
        $doc->load(self::samplesDir() . 'custom/items/template_declaration_default.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);

        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();

        $this->assertTrue($itemSession['WRONGSCORE']->equals(new QtiFloat(0.0)));
        $this->assertTrue($itemSession['GOODSCORE']->equals(new QtiFloat(1.0)));

        // 1st attempt to get 'GOODSCORE' as 'SCORE'.
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $itemSession->beginAttempt();
        $itemSession->endAttempt($responses);
        $this->assertTrue($itemSession['SCORE']->equals($itemSession['GOODSCORE']));

        // 2nd attempt to get 'WRONGSCORE' as 'SCORE'.
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]);
        $itemSession->beginAttempt();
        $itemSession->endAttempt($responses);
        $this->assertTrue($itemSession['SCORE']->equals($itemSession['WRONGSCORE']));
    }

    public function testSimultaneousSubmissionOnlyOneAttempt()
    {
        // We want to test that if the current submission mode is SIMULTANEOUS,
        // only one attempt is allowed.
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->setSubmissionMode(SubmissionMode::SIMULTANEOUS);

        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $itemSession->beginItemSession();
        $this->assertEquals(1, $itemSession->getRemainingAttempts());

        $itemSession->beginAttempt();
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $itemSession->endAttempt(new State());

        $this->assertEquals(0, $itemSession->getRemainingAttempts());
    }

    public function testSetOutcomeValuesWithSum()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/set_outcome_values_with_sum.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();

        $responses = new State([new ResponseVariable('response-X', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceB'), new QtiIdentifier('ChoiceC')]))]);
        $itemSession->endAttempt($responses);

        $this->assertEquals(1., $itemSession['score-X']->getValue());
    }

    public function testSetOutcomeValuesWithSumJuggling()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/set_outcome_values_with_sum_juggling.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();

        $responses = new State([new ResponseVariable('response-X', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceB'), new QtiIdentifier('ChoiceC')]))]);
        $itemSession->endAttempt($responses);

        $this->assertEquals(1., $itemSession['score-X']->getValue());
    }

    public function testSuspendWithResponses()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();

        $itemSession->suspend(
            new State(
                [
                    new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')),
                ]
            )
        );

        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $itemSession->getState());
        $this->assertEquals('ChoiceA', $itemSession['RESPONSE']->getValue());
        $this->assertEquals(0., $itemSession['SCORE']->getValue());
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());

        $itemSession->beginCandidateSession();

        $itemSession->suspend(
            new State(
                [
                    new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')),
                ]
            )
        );

        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $itemSession->getState());
        $this->assertEquals('ChoiceB', $itemSession['RESPONSE']->getValue());
        $this->assertEquals(0., $itemSession['SCORE']->getValue());
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());

        $itemSession->beginCandidateSession();

        // Finall, the candidates decide to validate its last choice. So no new responses to provided.
        $itemSession->endAttempt(new State());

        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertEquals('ChoiceB', $itemSession['RESPONSE']->getValue());
        $this->assertEquals(1., $itemSession['SCORE']->getValue());
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
    }

    public function testIsRespondedTextEntry()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/text_entry.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = $itemSession->getItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);
        $itemSession->beginItemSession();

        // Respond with a null value.
        $itemSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::STRING)]);
        $itemSession->endAttempt($responses);

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Respond with an empty string.
        $itemSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::STRING, new QtiString(''))]);
        $itemSession->endAttempt($responses);

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Respond with a non-empty string.
        $itemSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::STRING, new QtiString('York'))]);
        $itemSession->endAttempt($responses);

        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
    }

    public function testMultipleAttemptsSimultaneousSubmissionMode()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->setSubmissionMode(SubmissionMode::SIMULTANEOUS);
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage("A new attempt for item 'Q01' is not allowed. The submissionMode is simultaneous and the only accepted attempt is already begun.");

        // Beginning a 2nd attempt in simultaneous submission mode must throw an exception. Only a single attempt is accepted.
        $itemSession->beginAttempt();
    }

    public function testMultipleAttemptsIndividualSubmissionModeWhenSingleAttemptAllowed()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(1);
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage("A new attempt for item 'Q01' is not allowed. The maximum number of attempts (1) is reached.");
        $this->expectExceptionCode(AssessmentItemSessionException::ATTEMPTS_OVERFLOW);

        $itemSession->beginAttempt();
    }

    public function testBeginAttemptAdaptiveCompletionStatusComplete()
    {
        $itemSession = $this->instantiateBasicAdaptiveAssessmentItem();
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $itemSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage("A new attempt for item 'Q01' is not allowed. It is adaptive and its completion status is 'completed'.");

        $itemSession->beginAttempt();
    }

    public function testSuspendStateViolation()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage("Cannot switch from state NOTSELECTED to state SUSPENDED.");

        $itemSession->suspend();
    }

    public function testBeginCandidateSessionStateViolation()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage("Cannot switch from state NOTSELECTED to state INTERACTING.");

        $itemSession->beginCandidateSession();
    }

    public function testEndCandidateSessionStateViolation()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();

        $this->expectException(AssessmentItemSessionException::class);
        $this->expectExceptionMessage("Cannot switch from state NOTSELECTED to state SUSPENDED.");

        $itemSession->endCandidateSession();
    }

    public function testIsRespondedValueNullDefaultNotNull()
    {
        $itemSession = $this->instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $itemSession->getVariable('RESPONSE')->setDefaultValue(new QtiIdentifier('ChoiceA'));

        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
    }

    public function testIsRespondedMultipleInteractions1()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/is_responded/is_responded_multiple_interactions_singlechoice_textentry.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = $itemSession->getItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);

        $itemSession->beginItemSession();

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 1. Just respond nothing.
        $itemSession->beginAttempt();

        // Right after beginning the first attempt:
        // - RESPONSEA has value "ChoiceC" as it is its default value.
        // - RESPONSEB has a null value.

        $this->assertEquals('ChoiceC', $itemSession['RESPONSEA']->getValue());
        $this->assertNull($itemSession['RESPONSEB']);

        $itemSession->endAttempt(
            new State()
        );

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 2. Just respond with an empty string to the textEntryInteraction.
        // (Note: in QTI, empty strings, empty containers and null are considered equal values).
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSEB',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('')
                ),
            ])
        );

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 3. Just respond to the textEntryInteraction with a non empty string.
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSEB',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('Lorem Ipsum')
                ),
            ])
        );

        $this->assertTrue($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 4. Respond to the ChoiceInteraction.
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSEA',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('ChoiceA')
                ),
            ])
        );

        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
    }

    public function testIsRespondedMultipleInteractions2()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/is_responded/is_responded_multiple_interactions_multiplechoice_textentry.xml');

        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = $itemSession->getItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);

        $itemSession->beginItemSession();

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 1. Just respond nothing.
        $itemSession->beginAttempt();

        // Right after beginning the first attempt:
        // - RESPONSEA has value ["ChoiceC"] as it is its default value.
        // - RESPONSEB has a null value.

        $this->assertEquals('ChoiceC', $itemSession['RESPONSEA'][0]->getValue());
        $this->assertNull($itemSession['RESPONSEB']);

        $itemSession->endAttempt(
            new State()
        );

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 2. Just respond with an empty string to the textEntryInteraction.
        // (Note: in QTI, empty strings, empty containers and null are considered equal values).
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSEB',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('')
                ),
            ])
        );

        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 3. Just respond to the textEntryInteraction with a non empty string.
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSEB',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('Lorem Ipsum')
                ),
            ])
        );

        $this->assertTrue($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));

        // Attempt 4. Respond to the ChoiceInteraction.
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSEA',
                    Cardinality::MULTIPLE,
                    BaseType::IDENTIFIER,
                    new MultipleContainer(
                        BaseType::IDENTIFIER,
                        [new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB')]
                    )
                ),
            ])
        );

        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
    }
}
