<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionResponseValidationTest
 */
class AssessmentTestSessionResponseValidationTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testValidateResponseValidateSkippingAllowedLinearIndividual(): void
    {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/validate_response/validate_skipping_allowed_linear_individual.xml');
        $testSession->beginTestSession();

        // - Q01 (minConstraint = 0, maxConstraint = 1)

        // Q01 - By providing a response of cardinality 2, an exception will be thrown.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB'),
                                ]
                            )
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q01).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q01.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q01.RESPONSE']);
        }

        // Q01 - Provide a null response to Q01 in order to end the attempt (skipping allowed).
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [

                            ]
                        )
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q01.RESPONSE']->equals(new MultipleContainer(BaseType::IDENTIFIER, [])));

        $testSession->moveNext();

        // - Q02 (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q02 - By providing an invalid string regarding the patternMask, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            new QtiString('AAAAA')
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q02).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q02.RESPONSE']);
        }

        // Q02 - By providing a NULL response, I will get an exception.
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            null
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q02).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q02.RESPONSE']);
        }

        // Q02 - By providing no RESPONSE variable, I will get an exception.
        try {
            $testSession->endAttempt(
                new State(
                    []
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q02).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q02.RESPONSE']);
        }

        // Q02 - Provide a valid response to Q02 in order to end the attempt.
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        new QtiString('aaaaa')
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q02.RESPONSE']->equals(new QtiString('aaaaa')));

        $testSession->moveNext();

        // - Q03  (minConstraint = 0, maxConstraint = 1) and (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q03 - By providing invalid responses to both RESPONSE1 and RESPONSE2, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB'),
                                ]
                            )
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            new QtiString('AAAAA')
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q03).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }

        // Q03 - By providing an invalid response for RESPONSE1 only, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB'),
                                ]
                            )
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            new QtiString('aaaaa')
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q03).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }

        // Q03 - By providing an invalid response for RESPONSE1, but no RESPONSE2 variable, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                ]
                            )
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q03).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }

        // Q03 - Provide a valid responses to Q03 in order to end the attempt.
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE1',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [
                                new QtiIdentifier('ChoiceA'),
                            ]
                        )
                    ),
                    new ResponseVariable(
                        'RESPONSE2',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        new QtiString('aaaaa')
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q03.RESPONSE1']->equals(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA')])));
        $this::assertTrue($testSession['Q03.RESPONSE2']->equals(new QtiString('aaaaa')));

        $testSession->moveNext();

        // - Q04  (minConstraint = 1, maxConstraint = 4, identifier "ChoiceA" can only appear 1 or 2 times)

        // Q04 - By providing an invalid response, I will get an exception because I have 3 times "ChoiceA".
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::MULTIPLE,
                            BaseType::PAIR,
                            new MultipleContainer(
                                BaseType::PAIR,
                                [
                                    new QtiPair('ChoiceA', 'ChoiceB'),
                                    new QtiPair('ChoiceA', 'ChoiceC'),
                                    new QtiPair('ChoiceA', 'ChoiceD'),
                                ]
                            )
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q04).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q04.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q04.RESPONSE']);
        }

        // Q04 - Provide a valid responses to Q04 in order to end the attempt.
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::MULTIPLE,
                        BaseType::PAIR,
                        new MultipleContainer(
                            BaseType::PAIR,
                            [
                                new QtiPair('ChoiceA', 'ChoiceB'),
                                new QtiPair('ChoiceA', 'ChoiceC'),
                            ]
                        )
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q04.RESPONSE']->equals(new MultipleContainer(BaseType::PAIR, [new QtiPair('ChoiceA', 'ChoiceB'), new QtiPair('ChoiceA', 'ChoiceC')])));

        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
    }

    public function testValidateResponseValidateSkippingNotAllowedLinearIndividual(): void
    {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/validate_response/validate_skipping_not_allowed_linear_individual.xml');
        $testSession->beginTestSession();

        // - Q01 (minConstraint = 0, maxConstraint = 1)

        // Q01 - By providing a response of cardinality 2, an exception will be thrown.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB'),
                                ]
                            )
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q01).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q01.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q01.RESPONSE']);
        }

        // Q01 - By providing a null response to Q01, I will get an exception because skipping is not allowed, even
        // if the response validity constraints are respected...
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER
                            )
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q01).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this::assertEquals("The Item Session 'Q01.0' is not allowed to be skipped.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q01.RESPONSE']);
        }

        // Q01 - Provide a non-null response to Q01 in order to end the attempt, because skipping is not allowed.
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [
                                new QtiIdentifier('ChoiceA'),
                            ]
                        )
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q01.RESPONSE']->equals(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA')])));

        $testSession->moveNext();

        // - Q02 (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q02 - By providing an invalid string regarding the patternMask, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            new QtiString('AAAAA')
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q02).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q02.RESPONSE']);
        }

        // Q02 - By providing a NULL response, I will get an exception.
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            null
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q02).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q02.RESPONSE']);
        }

        // Q02 - By providing no RESPONSE variable, I will get an exception.
        try {
            $testSession->endAttempt(
                new State(
                    []
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q02).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q02.RESPONSE']);
        }

        // Q02 - Provide a valid response to Q02 in order to end the attempt.
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        new QtiString('aaaaa')
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q02.RESPONSE']->equals(new QtiString('aaaaa')));

        $testSession->moveNext();

        // - Q03  (minConstraint = 0, maxConstraint = 1) and (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q03 - By providing invalid responses to both RESPONSE1 and RESPONSE2, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB'),
                                ]
                            )
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            new QtiString('AAAAA')
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q03).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }

        // Q03 - By providing an invalid response for RESPONSE1 only, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB'),
                                ]
                            )
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            new QtiString('aaaaa')
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q03).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }

        // Q03 - By providing a valid response for RESPONSE1, but no RESPONSE2 variable, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                [
                                    new QtiIdentifier('ChoiceA'),
                                ]
                            )
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q03).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this::assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }

        // Q03 - Provide a valid responses to Q03 in order to end the attempt.
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE1',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [
                                new QtiIdentifier('ChoiceA'),
                            ]
                        )
                    ),
                    new ResponseVariable(
                        'RESPONSE2',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        new QtiString('aaaaa')
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q03.RESPONSE1']->equals(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA')])));
        $this::assertTrue($testSession['Q03.RESPONSE2']->equals(new QtiString('aaaaa')));

        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
    }

    public function testValidateResponseDoNotValidateSkippingAllowedLinearIndividual(): void
    {
        // Here I can do what I want because responses are not validated and skipping is allowed!!!

        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/validate_response/dont_validate_skipping_allowed_linear_individual.xml');
        $testSession->beginTestSession();

        // - Q01 (minConstraint = 0, maxConstraint = 1)

        // Q01 - Providing a response with cardinality 2 will work as validateResponses = false.
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [
                                new QtiIdentifier('ChoiceA'),
                                new QtiIdentifier('ChoiceB'),
                            ]
                        )
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q01.RESPONSE']->equals(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB')])));

        $testSession->moveNext();

        // - Q02 (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q02 - By providing an invalid string regarding the patternMask, I will get no exception, because validateResponses = false.
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        new QtiString('AAAAA')
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q02.RESPONSE']->equals(new QtiString('AAAAA')));

        $testSession->moveNext();

        // - Q03  (minConstraint = 0, maxConstraint = 1) and (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q03 - By providing a null response to both variables, it's OK because I can skip and provide invalid responses!!!
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE1',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            []
                        )
                    ),
                    new ResponseVariable(
                        'RESPONSE2',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        null
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q03.RESPONSE1']->equals(new MultipleContainer(BaseType::IDENTIFIER, [])));
        $this::assertNull($testSession['Q03.RESPONSE2']);

        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
    }

    public function testValidateResponseDoNotValidateSkippingNotAllowedLinearIndividual(): void
    {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/validate_response/dont_validate_skipping_not_allowed_linear_individual.xml');
        $testSession->beginTestSession();

        // - Q01 (minConstraint = 0, maxConstraint = 1)

        // Q01 - Providing a response with cardinality 2 will work as validateResponses = false.
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [
                                new QtiIdentifier('ChoiceA'),
                                new QtiIdentifier('ChoiceB'),
                            ]
                        )
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q01.RESPONSE']->equals(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB')])));

        $testSession->moveNext();

        // - Q02 (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q02 - By providing an invalid string regarding the patternMask, I will get no exception, because validateResponses = false.
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        new QtiString('AAAAA')
                    ),
                ]
            )
        );

        $this::assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this::assertTrue($testSession['Q02.RESPONSE']->equals(new QtiString('AAAAA')));

        $testSession->moveNext();

        // - Q03  (minConstraint = 0, maxConstraint = 1) and (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q03 - By providing a null response to both variables, I get an exception because skipping is not allowed
        $testSession->beginAttempt();

        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                []
                            )
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            null
                        ),
                    ]
                )
            );

            $this::assertFalse(true, 'An exception should be thrown (Q03).');
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this::assertEquals("The Item Session 'Q03.0' is not allowed to be skipped.", $e->getMessage());
            $this::assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }

        // Q03 - By providing a null response to RESPONSE1 should throw an exception
        try {
            $testSession->endAttempt(
                new State(
                    [
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            null
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE,
                            BaseType::STRING,
                            new QtiString('a')
                        ),
                    ]
                )
            );
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this::assertEquals("The Item Session 'Q03.0' is not allowed to be skipped.", $e->getMessage());
            $this::assertNull($testSession['Q03.RESPONSE1']);
            $this::assertNull($testSession['Q03.RESPONSE2']);
        }


        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
    }

    public function testNonLinearSimultaneous(): void
    {
        // I can do what I want because I'm in simultaneous submission mode, where allowSkipping and validateResponse
        // are ignored attributes.

        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/validate_response/nonlinear_simultaneous.xml');
        $testSession->beginTestSession();

        // - Q01 (minConstraint = 0, maxConstraint = 1)

        // Q01 - Providing a response with invalid cardinality is all right because I'm in simultaneous submission mode !
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [
                                new QtiIdentifier('ChoiceA'),
                                new QtiIdentifier('ChoiceB'),
                            ]
                        )
                    ),
                ]
            )
        );

        // I'm in simultaneous submission mode, so don't forget that item sessions goes from interactive to supspended state only.
        $this::assertEquals(AssessmentItemSessionState::SUSPENDED, $testSession->getCurrentAssessmentItemSession()->getState());
        // I'm in simultaneous submission mode, so responses are submitted only at the end of the test part.
        $this::assertNull($testSession['Q01.RESPONSE']);

        $testSession->moveNext();

        // - Q02 (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q02 - By providing an empty (null) response, it's still ok because I'm in simultaneous submission mode !
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        new QtiString('')
                    ),
                ]
            )
        );

        // I'm in simultaneous submission mode, so don't forget that item sessions goes from interactive to supspended state only.
        $this::assertEquals(AssessmentItemSessionState::SUSPENDED, $testSession->getCurrentAssessmentItemSession()->getState());
        // I'm in simultaneous submission mode, so responses are submitted only at the end of the test part.
        $this::assertNull($testSession['Q02.RESPONSE']);

        $testSession->moveNext();

        // - Q03  (minConstraint = 0, maxConstraint = 1) and (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})

        // Q03 - It's OK to provide an invalid response + a null response as I'm in simultaneous submission mode (yeah) !
        $testSession->beginAttempt();
        $testSession->endAttempt(
            new State(
                [
                    new ResponseVariable(
                        'RESPONSE1',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            [
                                new QtiIdentifier('ChoiceA'),
                                new QtiIdentifier('ChoiceB'),
                            ]
                        )
                    ),
                    new ResponseVariable(
                        'RESPONSE2',
                        Cardinality::SINGLE,
                        BaseType::STRING,
                        null
                    ),
                ]
            )
        );

        // I'm in simultaneous submission mode, so don't forget that item sessions goes from interactive to supspended state only.
        $this::assertEquals(AssessmentItemSessionState::SUSPENDED, $testSession->getCurrentAssessmentItemSession()->getState());
        // I'm in simultaneous submission mode, so responses are submitted only at the end of the test part.
        $this::assertNull($testSession['Q03.RESPONSE1']);
        $this::assertNull($testSession['Q03.RESPONSE2']);

        $testSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
        $this::assertTrue($testSession['Q01.RESPONSE']->equals(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB')])));
        $this::assertTrue($testSession['Q02.RESPONSE']->equals(new QtiString('')));
        $this::assertTrue($testSession['Q03.RESPONSE1']->equals(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB')])));
        $this::assertNull($testSession['Q03.RESPONSE2']);

        foreach ($testSession->getAssessmentItemSessionStore()->getAllAssessmentItemSessions() as $itemSession) {
            $this::assertEquals(1, $itemSession['numAttempts']->getValue());
            $this::assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        }
    }
}
