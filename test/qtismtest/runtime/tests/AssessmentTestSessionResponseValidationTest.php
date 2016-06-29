<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentTestSessionException;

class AssessmentTestSessionResponseValidationTest extends QtiSmAssessmentTestSessionTestCase {
	
    public function testValidateResponseSkippingAllowedLinearIndividual() {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/validate_response/skipping_allowed_linear_individual.xml');
        $testSession->beginTestSession();
        
        // - Q01 (minConstraint = 0, maxConstraint = 1)
        
        // By providing a response of cardinality 2, an exception will be thrown.
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
            
            $this->assertFalse(true, "An exception should be thrown.");
            
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_INVALID_RESPONSE, $e->getCode());
            $this->assertEquals("An invalid response was given for Item Session 'Q01.0' while 'itemSessionControl->validateResponses' is in force.", $e->getMessage());
        }
    }
}
