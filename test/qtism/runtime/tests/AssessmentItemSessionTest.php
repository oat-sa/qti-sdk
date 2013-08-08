<?php
use qtism\data\TimeLimits;

use qtism\data\ItemSessionControl;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\common\State;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\data\storage\xml\marshalling\ExtendedAssessmentItemRefMarshaller;

class AssessmentItemSessionTest extends QtiSmTestCase {
	
    public function testInstantiation() {
        
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        // No timelimits by default.
        $this->assertFalse($itemSession->hasTimeLimits());
        
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
        $this->assertEquals(BaseType::IDENTIFIER, $itemSession->getVariable('completionStatus')->getBaseType());
        
        // State is correct?
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $itemSession->getState());
    }
    
    public function testEvolutionBasic() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        $itemSession->beginAttempt();
        // when the first attempt occurs, the response variable must get their default value.
        // in our case, no default value. The RESPONSE variable must remain NULL.
        $this->assertSame(null, $itemSession['RESPONSE']);
        $this->assertEquals(1, $itemSession['numAttempts']);
        
        // Now, we end the attempt by providing a set of responses for the attempt. Response
        // processing will take place.
        
        // Note: here we provide a State object for the responses, but the value of the 'RESPONSE'
        // variable can also be set manually on the item session prior calling endAttempt(). This
        // is a matter of choice.
        $resp = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceB');
        $itemSession->endAttempt(new State(array($resp)));
        
        // The ItemSessionControl for this session was not specified, it is then
        // the default one, with default values. Because maxAttempts is not specified,
        // it is considered to be 1, because the item is non-adaptive.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertEquals('completed', $itemSession['completionStatus']);
        $this->assertEquals(1, $itemSession['numAttempts']);
        
        // If we now try to begin a new attempt, we get a logic exception.
        try {
            $itemSession->beginAttempt();
            
            // An exception MUST be thrown.
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::MAX_ATTEMPTS_EXCEEDED, $e->getCode());
        }
    }
    
    /**
     * 
     * @param integer $count The number of attempts to perform.
     * @param array $attempts An array of string which are QTI identifiers, corresponding to a choice.
     * @param array $expected The expected SCORE value in relation with the $attempts
     */
    public function testEvolutionBasicMultipleAttempts() {
        
        $count = 5;
        $attempts = array('ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD', 'ChoiceE');
        $expected = array(0.0, 1.0, 0.0, 0.0, 0.0);
        
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts($count);
        $itemSession->setItemSessionControl($itemSessionControl);
        
        for ($i = 0; $i < $count; $i++) {
            // Here, manual set up of responses.
            $itemSession->beginAttempt();
            
            // simulate some time... 1 second to answer the item.
            sleep(1);
            
            $itemSession['RESPONSE'] = $attempts[$i];
            $itemSession->endAttempt();
            $this->assertInternalType('float', $itemSession['SCORE']);
            $this->assertEquals($expected[$i], $itemSession['SCORE']);
            $this->assertEquals($i + 1, $itemSession['numAttempts']);
            
            // 1 more second before the next attempt.
            // we are here in suspended mode so it will not be
            // added to the duration.
            sleep(1);
        }
        
        // The total duration shold have taken 5 seconds, the rest of the time was in SUSPENDED state.
        $this->assertEquals(5, $itemSession['duration']->getSeconds(true));
        
        // one more and we get an expection... :)
        try {
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::MAX_ATTEMPTS_EXCEEDED, $e->getCode());
        }
    }
    
    public function testEvolutionAdaptiveItem() {
        $itemSession = self::instantiateBasicAdaptiveAssessmentItem();
        
        // reminder, the value of maxAttempts is ignored when dealing with
        // adaptive items.
        
        // First attempt, just fail the item.
        $itemSession->beginAttempt();
        $itemSession['RESPONSE'] = 'ChoiceE';
        $itemSession->endAttempt();
        
        $this->assertEquals(1, $itemSession['numAttempts']);
        $this->assertEquals('incomplete', $itemSession['completionStatus']);
        $this->assertInternalType('float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']);
        
        // Second attempt, give the correct answer to be allowed to go to the next item.
        $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceB'))));
        $this->assertEquals('completed', $itemSession['completionStatus']);
        $this->assertInternalType('float', $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']);
        
        // If you now try to attempt again, exception because already completed.
        
        try {
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::MAX_ATTEMPTS_EXCEEDED, $e->getCode());
        }
    }
    
    public function testDurationBrutalSessionClosing() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $this->assertEquals($itemSession['duration']->__toString(), 'PT0S');
        
        $itemSession->beginAttempt();
        sleep(1);
        
        // End session while attempting (brutal x))
        $itemSession->endItemSession();
        $this->assertEquals($itemSession['duration']->__toString(), 'PT1S');
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
     * @return AssessmentItemSession
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
    
    /**
     * Instantiate a basic item session for an adaptive, non-timeDependent item with two variables:
     * 
     * * RESPONSE (single, identifier, correctResponse = 'ChoiceB'
     * * SCORE (single, float, defaultValue = 0.0)
     * 
     * The responseProcessing sets:
     * 
     * * SCORE to 0, completionStatus to 'incomplete', if the response is not 'ChoiceB'.
     * * SCORE to 1, completionStatus to 'complete', if the response is 'ChoiceB'.
     * 
     * @return \qtism\runtime\tests\AssessmentItemSession
     */
    private static function instantiateBasicAdaptiveAssessmentItem() {
        $itemRef = self::createExtendedAssessmentItemRefFromXml('
            <assessmentItemRef identifier="Q01" href="./Q01.xml" adaptive="true" timeDependent="false">
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
                
                <!-- The candidate is allowed to attempt the item until he provides the correct answer -->
                <responseProcessing>
                    <responseCondition>
                        <responseIf>
                            <match>
                                <variable identifier="RESPONSE"/>
                                <baseValue baseType="identifier">ChoiceB</baseValue>
                            </match>
                            <setOutcomeValue identifier="SCORE">
                                <baseValue baseType="float">1</baseValue>
                            </setOutcomeValue>
                            <setOutcomeValue identifier="completionStatus">
                                <baseValue baseType="identifier">completed</baseValue>
                            </setOutcomeValue>
                        </responseIf>
                        <responseElse>
                            <setOutcomeValue identifier="SCORE">
                                <baseValue baseType="float">0</baseValue>
                            </setOutcomeValue>
                            <setOutcomeValue identifier="completionStatus">
                                <baseValue baseType="identifier">incomplete</baseValue>
                            </setOutcomeValue>
                        </responseElse>
                    </responseCondition>
                </responseProcessing>
            </assessmentItemRef>
        ');
        
        return new AssessmentItemSession($itemRef);
    }
}