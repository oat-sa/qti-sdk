<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\data\storage\xml\marshalling\ExtendedAssessmentItemRefMarshaller;

class AssessmentItemSessionTest extends QtiSmTestCase {
	
    public function testInstantiation() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        // Response variables instantiated and set to NULL?
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('RESPONSE'));
        $this->assertSame(null, $itemSession['RESPONSE']);
        
        // Outcome variables instantiated and set to their default if any?
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $itemSession->getVariable('SCORE'));
        $this->assertInternalType('float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']);
        
        // Built-in variables instantiated and values initialized correctly?
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('numAttempts'));
        $this->assertInternalType('integer', $itemSession['numAttempts']);
        $this->assertEquals(0, $itemSession['numAttempts']);
        
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('duration'));
        $this->assertInstanceOf('qtism\\common\\datatypes\\Duration', $itemSession['duration']);
        $this->assertEquals('PT0S', $itemSession['duration']->__toString());
        
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $itemSession->getVariable('completionStatus'));
        $this->assertInternalType('string', $itemSession['completionStatus']);
        $this->assertEquals('not_attempted', $itemSession['completionStatus']);
        
        // State is correct?
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $itemSession->getState());
    }
    
    private static function createExtendedAssessmentItemRefFromXml($xmlString) {
        $marshaller = new ExtendedAssessmentItemRefMarshaller();
        $element = self::createDOMElement($xmlString);
        return $marshaller->unmarshall($element);
    }
    
    /**
     * Instantiate a basic item session for a non-adaptive, non-timeDependent item with two variables:
     * 
     * * RESPONSE (single, identifier, correctResponse = 'ChoiceB')
     * * SCORE (single, float, defaultValue = 0.0)
     * 
     * The responseProcessing for item of the session is the template 'match_correct'.
     * 
     * @return \qtism\runtime\tests\AssessmentItemSession
     */
    private static function instantiateBasicAssessmentItemSession() {
        $itemRef = self::createExtendedAssessmentItemRefFromXml('
            <assessmentItemRef identifier="Q01" href="./Q01.xml" adaptive="false" timeDependent="false">
                <responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
					<correctResponse>
						<value>ChoiceB</value>
					</correctResponse>
				</responseDeclaration>
                <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
					<defaultValue>
						<value>0.0</value>
					</defaultValue>
				</outcomeDeclaration>
                <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
            </assessmentItemRef>
        ');
        
        return new AssessmentItemSession($itemRef);
    }
}