<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\data\storage\xml\XmlAssessmentItemDocument;
use qtism\common\datatypes\Duration;
use qtism\data\TimeLimits;
use qtism\data\ItemSessionControl;
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
        
        // isPresented? isCorrect? isResponded? isSelected?
        $this->assertFalse($itemSession->isPresented());
        $this->assertFalse($itemSession->isCorrect());
        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isSelected());
        
        $itemSession->beginItemSession();
        // After beginItemSession...
        // isPresented? isCorrect? isResponded? isSelected?
        $this->assertFalse($itemSession->isPresented());
        $this->assertFalse($itemSession->isCorrect());
        $this->assertFalse($itemSession->isResponded());
        $this->assertTrue($itemSession->isSelected());
        $this->assertTrue($itemSession->isAttemptable());
        
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
        
        // Remaining attempts correct?
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $this->assertTrue($itemSession->isAttemptable());
    }
    
    public function testEvolutionBasic() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();
        $this->assertTrue($itemSession->isSelected());
        
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $this->assertTrue($itemSession->isAttemptable());
        $itemSession->beginAttempt();
        $this->assertEquals(1, $itemSession['numAttempts']);
        $this->assertTrue($itemSession->isPresented());
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
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
        $this->assertTrue($itemSession->isResponded());
        
        // The ItemSessionControl for this session was not specified, it is then
        // the default one, with default values. Because maxAttempts is not specified,
        // it is considered to be 1, because the item is non-adaptive.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertEquals('completed', $itemSession['completionStatus']);
        $this->assertEquals(1, $itemSession['numAttempts']);
        $this->assertTrue($itemSession->isCorrect());
        
        // If we now try to begin a new attempt, we get a logic exception.
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            
            // An exception MUST be thrown.
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::ATTEMPTS_OVERFLOW, $e->getCode());
        }
    }
    
    public function testGetResponseVariables() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
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
    
    public function testGetOutcomeVariables() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
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
    
    public function testEvolutionBasicTimeLimitsUnderflowOverflow() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        // Give more than one attempt.
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(2);
        $itemSession->setItemSessionControl($itemSessionControl);
        
        // No late submission allowed.
        $timeLimits = new TimeLimits(new Duration('PT1S'), new Duration('PT2S'));
        $itemSession->setTimeLimits($timeLimits);
        $itemSession->beginItemSession();
        
        // End the attempt before minTime of 1 second.
        $this->assertEquals(2, $itemSession->getRemainingAttempts());
        $itemSession->beginAttempt();
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        
        try {
            $itemSession->endAttempt();
            // An exception MUST be thrown.
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_UNDERFLOW, $e->getCode());
        }
        
        // Check that numAttempts is taken into account &
        // that the session is correctly suspended, waiting for
        // the next attempt.
        $this->assertEquals(1, $itemSession['numAttempts']);
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $itemSession->getState());
        
        // Try again by waiting too much to respect max time.
        $itemSession->beginAttempt();
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        sleep(3);
        
        try {
            $itemSession->endAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
        }
        
        $this->assertEquals(2, $itemSession['numAttempts']);
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertInternalType('float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']);
    }
    
    public function testAllowLateSubmissionNonAdaptive() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        $timeLimits = new TimeLimits(null, new Duration('PT1S'), true);
        $itemSession->setTimeLimits($timeLimits);
        
        $itemSession->beginItemSession();
        
        $itemSession->beginAttempt();
        $itemSession['RESPONSE'] = 'ChoiceB';
        sleep(2);
        
        // No exception because late submission is allowed.
        $itemSession->endAttempt();
        $this->assertEquals(1.0, $itemSession['SCORE']);
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
    }
    
    public function testAcceptableLatency() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSession->setAcceptableLatency(new Duration('PT1S'));
        
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(3);
        $itemSession->setItemSessionControl($itemSessionControl);
        
        $timeLimits = new TimeLimits(new Duration('PT1S'), new Duration('PT2S'));
        $itemSession->setTimeLimits($timeLimits);
        
        $itemSession->beginItemSession();
        
        // Sleep 3 second to respect minTime and stay in the acceptable latency time.
        $itemSession->beginAttempt();
        sleep(3);
        $itemSession->endAttempt();
        
        // Sleep 1 more second to achieve the attempt outside the time frame.
        $itemSession->beginAttempt();
        sleep(1);
        
        try {
            $itemSession->endAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
            $this->assertEquals('PT4S', $itemSession['duration']->__toString());
            $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
            $this->assertEquals(0, $itemSession->getRemainingAttempts());
        }
    }
    
   
    public function testEvolutionBasicMultipleAttempts() {
        
        $count = 5;
        $attempts = array('ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD', 'ChoiceE');
        $expected = array(0.0, 1.0, 0.0, 0.0, 0.0);
        
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts($count);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();
        
        for ($i = 0; $i < $count; $i++) {
            // Here, manual set up of responses.
            $this->assertTrue($itemSession->isAttemptable());
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
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::ATTEMPTS_OVERFLOW, $e->getCode());
        }
    }
    
    public function testEvolutionAdaptiveItem() {
        $itemSession = self::instantiateBasicAdaptiveAssessmentItem();
        $itemSession->beginItemSession();
        
        // reminder, the value of maxAttempts is ignored when dealing with
        // adaptive items.
        
        // First attempt, just fail the item.
        // We do not known how much attempts to complete.
        $this->assertTrue($itemSession->isAttemptable());
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        $itemSession->beginAttempt();
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        $itemSession['RESPONSE'] = 'ChoiceE';
        $itemSession->endAttempt();
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        
        $this->assertEquals(1, $itemSession['numAttempts']);
        $this->assertEquals('incomplete', $itemSession['completionStatus']);
        $this->assertInternalType('float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']);
        
        $itemSession->beginAttempt();
        // Second attempt, give the correct answer to be allowed to go to the next item.
        $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceB'))));
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $this->assertEquals('completed', $itemSession['completionStatus']);
        $this->assertInternalType('float', $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']);
        
        // If you now try to attempt again, exception because already completed.
        
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::ATTEMPTS_OVERFLOW, $e->getCode());
        }
    }
    
    public function testDurationBrutalSessionClosing() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();
        $this->assertEquals($itemSession['duration']->__toString(), 'PT0S');
        
        $this->assertTrue($itemSession->isAttemptable());
        $itemSession->beginAttempt();
        sleep(1);
        
        // End session while attempting (brutal x))
        $itemSession->endItemSession();
        $this->assertEquals($itemSession['duration']->__toString(), 'PT1S');
    }
    
    public function testValidateResponsesInForce() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setValidateResponses(true);
        $itemSession->setItemSessionControl($itemSessionControl);
        
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        // Set an invalid response.
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceC'));
        
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->endAttempt($responses);
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::INVALID_RESPONSE, $e->getCode());
            
            // The response must not be taken into account in the itemSession, because the mustValidateResponse attribute
            // prevents the item TO BE SUBMITTED if not all valid responses.
            $this->assertFalse($itemSession['RESPONSE'] === 'ChoiceC');
        }
    }
    
    public function testSkippingForbidden() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setAllowSkipping(false);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();
        
        $itemSession->beginAttempt();
        try {
            $itemSession->skip();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::SKIPPING_FORBIDDEN, $e->getCode());
        }
    }
    
    public function testSkippingAllowed() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();
        
        $itemSession->beginAttempt();
        $itemSession->skip();
        
        $this->assertEquals($itemSession->getState(), AssessmentItemSessionState::CLOSED);
        $this->assertEquals(0.0, $itemSession['SCORE']);
        $this->assertEquals(null, $itemSession['RESPONSE']);
    }
    
    public function testValidResponsesInForceValid() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setValidateResponses(false);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();
        
        $itemSession->beginAttempt();
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceD'));
        $itemSession->endAttempt($responses);
    }
    
    public function testIsCorrect() {
        $itemSession = self::instantiateBasicAdaptiveAssessmentItem();
        $this->assertEquals(AssessmentItemSessionState::NOT_SELECTED, $itemSession->getState());
        
        // The item session is in NOT_SELECTED mode, then false is returned directly.
        $this->assertFalse($itemSession->isCorrect());

        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        
        // No response given, false is returned.
        $this->assertFalse($itemSession->isCorrect());
        
        $state = new State();
        $state->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'));
        $itemSession->endAttempt($state);
        
        // Wrong answer ('ChoiceB' is the correct one), the session is not correct.
        $this->assertEquals('incomplete', $itemSession['completionStatus']);
        $this->assertFalse($itemSession->isCorrect());
        
        $state['RESPONSE'] = 'ChoiceB';
        $itemSession->beginAttempt();
        $itemSession->endAttempt($state);
        
        // Correct answer, the session is correct!
        $this->assertTrue($itemSession->isCorrect());
        $this->assertEquals('completed', $itemSession['completionStatus']);
    }
    
    public function testStandaloneItemSession() {
        $doc = new XmlAssessmentItemDocument('2.1');
        $doc->load(self::samplesDir() . 'ims/items/2_1/hotspot.xml');
        
        $itemSession = new AssessmentItemSession($doc);
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'A')));
        $itemSession->endAttempt($responses);
        $this->assertInternalType('float', $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']);
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