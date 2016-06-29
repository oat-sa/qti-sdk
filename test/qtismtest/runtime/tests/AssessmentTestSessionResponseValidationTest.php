<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestSessionState;

class AssessmentTestSessionResponseValidationTest extends QtiSmAssessmentTestSessionTestCase {
	
    public function testValidateResponseSkippingAllowedLinearIndividual() {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/validate_response/skipping_allowed_linear_individual.xml');
        $testSession->beginTestSession();
        
        // - Q01 (minConstraint = 0, maxConstraint = 1)
        
        // Q01 - By providing a response of cardinality 2, an exception will be thrown.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    array(
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::MULTIPLE, 
                            BaseType::IDENTIFIER, 
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                array(
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB')
                                )
                            )
                        )
                    )
                )
            );
            
            $this->assertFalse(true, "An exception should be thrown (Q01).");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q01.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this->assertNull($testSession['Q01.RESPONSE']);
        }
        
        // Q01 - Provide a valid response to Q01 in order to end the attempt.
        $testSession->endAttempt(
            new State(
                array(
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::MULTIPLE, 
                        BaseType::IDENTIFIER, 
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            array(
                                new QtiIdentifier('ChoiceA')
                            )
                        )
                    )
                )
            )
        );
        
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this->assertTrue($testSession['Q01.RESPONSE']->equals(new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceA')))));
        
        $testSession->moveNext();
        
        // - Q02 (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})
        
        // Q02 - By providing an invalid string regarding the patternMask, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    array(
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::SINGLE, 
                            BaseType::STRING, 
                            new QtiString('AAAAA')
                        )
                    )
                )
            );
            
            $this->assertFalse(true, "An exception should be thrown (Q02).");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this->assertNull($testSession['Q02.RESPONSE']);
        }
        
        // Q02 - By providing a NULL response, I will get an exception.
        try {
            $testSession->endAttempt(
                new State(
                    array(
                        new ResponseVariable(
                            'RESPONSE',
                            Cardinality::SINGLE, 
                            BaseType::STRING,
                            null
                        )
                    )
                )
            );
            
            $this->assertFalse(true, "An exception should be thrown (Q02).");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this->assertNull($testSession['Q02.RESPONSE']);
        }
        
        // Q02 - By providing no RESPONSE variable, I will get an exception.
        try {
            $testSession->endAttempt(
                new State(
                    array()
                )
            );
            
            $this->assertFalse(true, "An exception should be thrown (Q02).");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q02.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this->assertNull($testSession['Q02.RESPONSE']);
        }
        
        // Q02 - Provide a valid response to Q02 in order to end the attempt.
        $testSession->endAttempt(
            new State(
                array(
                    new ResponseVariable(
                        'RESPONSE',
                        Cardinality::SINGLE, 
                        BaseType::STRING,
                        new QtiString('aaaaa')
                    )
                )
            )
        );
        
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this->assertTrue($testSession['Q02.RESPONSE']->equals(new QtiString('aaaaa')));
        
        $testSession->moveNext();
        
        // - Q03  (minConstraint = 0, maxConstraint = 1) and (minConstraint = 1, maxConstraint = 1, patternMask = [a-z]{1,5})
        
        // Q03 - By providing invalid responses to both RESPONSE1 and RESPONSE2, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    array(
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                array(
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB')
                                )
                            )
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE, 
                            BaseType::STRING, 
                            new QtiString('AAAAA')
                        )
                    )
                )
            );
            
            $this->assertFalse(true, "An exception should be thrown (Q03).");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this->assertNull($testSession['Q03.RESPONSE1']);
            $this->assertNull($testSession['Q03.RESPONSE2']);
        }
        
        // Q03 - By providing an invalid response for RESPONSE1 only, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    array(
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                array(
                                    new QtiIdentifier('ChoiceA'),
                                    new QtiIdentifier('ChoiceB')
                                )
                            )
                        ),
                        new ResponseVariable(
                            'RESPONSE2',
                            Cardinality::SINGLE, 
                            BaseType::STRING, 
                            new QtiString('aaaaa')
                        )
                    )
                )
            );
            
            $this->assertFalse(true, "An exception should be thrown (Q03).");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this->assertNull($testSession['Q03.RESPONSE1']);
            $this->assertNull($testSession['Q03.RESPONSE2']);
        }
        
        // Q03 - By providing a valid response for RESPONSE1, but no RESPONSE2 variable, I will get an exception.
        $testSession->beginAttempt();
        try {
            $testSession->endAttempt(
                new State(
                    array(
                        new ResponseVariable(
                            'RESPONSE1',
                            Cardinality::MULTIPLE,
                            BaseType::IDENTIFIER,
                            new MultipleContainer(
                                BaseType::IDENTIFIER,
                                array(
                                    new QtiIdentifier('ChoiceA'),
                                )
                            )
                        )
                    )
                )
            );
            
            $this->assertFalse(true, "An exception should be thrown (Q03).");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q03.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
            $this->assertEquals(AssessmentItemSessionState::INTERACTING, $testSession->getCurrentAssessmentItemSession()->getState());
            $this->assertNull($testSession['Q03.RESPONSE1']);
            $this->assertNull($testSession['Q03.RESPONSE2']);
        }
        
        // Q03 - Provide a valid responses to Q03 in order to end the attempt.
        $testSession->endAttempt(
            new State(
                array(
                    new ResponseVariable(
                        'RESPONSE1',
                        Cardinality::MULTIPLE,
                        BaseType::IDENTIFIER,
                        new MultipleContainer(
                            BaseType::IDENTIFIER,
                            array(
                                new QtiIdentifier('ChoiceA')
                            )
                        )
                    ),
                    new ResponseVariable(
                        'RESPONSE2',
                        Cardinality::SINGLE, 
                        BaseType::STRING, 
                        new QtiString('aaaaa')
                    )
                )
            )
        );
        
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $testSession->getCurrentAssessmentItemSession()->getState());
        $this->assertTrue($testSession['Q03.RESPONSE1']->equals(new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceA')))));
        $this->assertTrue($testSession['Q03.RESPONSE2']->equals(new QtiString('aaaaa')));
        
        $testSession->moveNext();
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
    }
}
