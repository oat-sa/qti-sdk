<?php
require_once (dirname(__FILE__) . '/../../../QtiSmAssessmentItemTestCase.php');

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\runtime\tests\SessionManager;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\SubmissionMode;
use qtism\common\datatypes\QtiDuration;
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
use qtism\runtime\common\MultipleContainer;

class AssessmentItemSessionTest extends QtiSmAssessmentItemTestCase {
	
    public function testInstantiation() {
        
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        // isPresented? isCorrect? isResponded? isSelected?
        $this->assertFalse($itemSession->isPresented());
        $this->assertFalse($itemSession->isCorrect());
        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(true));
        $this->assertFalse($itemSession->isSelected());
        
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
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('RESPONSE'));
        $this->assertSame(null, $itemSession['RESPONSE']);
        
        // Outcome variables instantiated and set to their default if any?
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $itemSession->getVariable('SCORE'));
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
        
        // Built-in variables instantiated and values initialized correctly?
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('numAttempts'));
        $this->assertInstanceOf(QtiInteger::class, $itemSession['numAttempts']);
        $this->assertEquals(0, $itemSession['numAttempts']->getValue());
        
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('duration'));
        $this->assertInstanceOf(QtiDuration::class, $itemSession['duration']);
        $this->assertEquals('PT0S', $itemSession['duration']->__toString());
        
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $itemSession->getVariable('completionStatus'));
        $this->assertInstanceOf(QtiString::class, $itemSession['completionStatus']);
        $this->assertEquals('not_attempted', $itemSession['completionStatus']->getValue());
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
        $itemSession->endAttempt(new State(array($resp)));
        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
        
        // The ItemSessionControl for this session was not specified, it is then
        // the default one, with default values. Because maxAttempts is not specified,
        // it is considered to be 1, because the item is non-adaptive.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertTrue($itemSession->isCorrect());
        
        // If we now try to begin a new attempt, we get a logic exception.
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            
            // An exception MUST be thrown.
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::STATE_VIOLATION, $e->getCode());
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
        $itemSession['RESPONSE'] = new QtiIdentifier('ChoiceE');
        $itemSession->endAttempt();
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertEquals('incomplete', $itemSession['completionStatus']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
        
        $itemSession->beginAttempt();
        // Second attempt, give the correct answer to be allowed to go to the next item.
        $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());
        
        // If you now try to attempt again, exception because already completed.
        
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            // The session is closed, you cannot begin another attempt.
            $this->assertEquals(AssessmentItemSessionException::STATE_VIOLATION, $e->getCode());
        }
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
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC')));
        
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->endAttempt($responses);
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::INVALID_RESPONSE, $e->getCode());
            
            // The response must not be taken into account in the itemSession, because the mustValidateResponse attribute
            // prevents the item TO BE SUBMITTED if not all valid responses.
            $this->assertSame(null, $itemSession['RESPONSE']);
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
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
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
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD')));
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
    
    public function testStandaloneItemSession() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/hotspot.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('A'))));
        $itemSession->endAttempt($responses);
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());
    }
    
    public function testStandaloneMultipleInteractions() {
        $doc = new XmlDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/items/multiple_interactions.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $this->assertInstanceOf(QtiFloat::class, $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
        
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('Choice_3'))));
        $itemSession->endAttempt($responses);
        $this->assertEquals(6.0, $itemSession['SCORE']->getValue());
    }
    
    public function testSimultaneousSubmissionOnlyOneAttempt() {
        // We want to test that if the current submission mode is SIMULTANEOUS,
        // only one attempt is allowed.
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSession->setSubmissionMode(SubmissionMode::SIMULTANEOUS);
        
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $itemSession->beginItemSession();
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        
        $itemSession->beginAttempt();
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $itemSession->skip();
        
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        
        // Another attempt must lead to an exception.
        try {
            $itemSession->beginAttempt();
            $this->assertTrue(false, 'Nore more attempts should be allowed.');
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::STATE_VIOLATION, $e->getCode());
        }
    }
    
    public function testRunCallback() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSession->beginItemSession();
        
        $itemSession->registerCallback('beginAttempt', function ($item, $itemSessionTest, $itemSession) {
                $itemSessionTest->assertEquals($item, $itemSession);
                $itemSessionTest->assertEquals($item->getState(), AssessmentItemSessionState::INTERACTING);
            },
            array($this, $itemSession)
        );
        
        $itemSession->registerCallback('suspend', function ($item, $itemSessionTest, $itemSession) {
                $itemSessionTest->assertEquals($item, $itemSession);
                $itemSessionTest->assertEquals($item->getState(), AssessmentItemSessionState::SUSPENDED);
            },
            array($this, $itemSession)
        );
            
        $itemSession->registerCallback('interact', function ($item, $itemSessionTest, $itemSession) {
                $itemSessionTest->assertEquals($item, $itemSession);
                $itemSessionTest->assertEquals($item->getState(), AssessmentItemSessionState::INTERACTING);
            },
            array($this, $itemSession)
        );
        
        $itemSession->registerCallback('endAttempt', function ($item, $itemSessionTest, $itemSession) {
                $itemSessionTest->assertEquals($item, $itemSession);
                $itemSessionTest->assertEquals($item->getState(), AssessmentItemSessionState::CLOSED);
            },
            array($this, $itemSession)
        );
        
        $itemSession->beginAttempt();
        $itemSession->suspend();
        $itemSession->interact();
        $itemSession->endAttempt();
    }
    
    public function testSetOutcomeValuesWithSum() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/set_outcome_values_with_sum.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        
        $responses = new State(array(new ResponseVariable('response-X', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceB'), new QtiIdentifier('ChoiceC'))))));
        $itemSession->endAttempt($responses);
        
        $this->assertEquals(1., $itemSession['score-X']->getValue());
    }
    
    public function testSetOutcomeValuesWithSumJuggling() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/set_outcome_values_with_sum_juggling.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        
        $responses = new State(array(new ResponseVariable('response-X', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceB'), new QtiIdentifier('ChoiceC'))))));
        $itemSession->endAttempt($responses);
        
        $this->assertEquals(1., $itemSession['score-X']->getValue());
    }
    
    public function testIsRespondedTextEntry() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/text_entry.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager());
        $itemSessionControl = $itemSession->getItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);
        $itemSession->beginItemSession();
        
        // Respond with a null value.
        $itemSession->beginAttempt();
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::STRING)));
        $itemSession->endAttempt($responses);
        
        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));
        
        // Respond with an empty string.
        $itemSession->beginAttempt();
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::STRING, new QtiString(''))));
        $itemSession->endAttempt($responses);
        
        $this->assertFalse($itemSession->isResponded());
        $this->assertFalse($itemSession->isResponded(false));
        
        // Respond with a non-empty string.
        $itemSession->beginAttempt();
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::STRING, new QtiString('York'))));
        $itemSession->endAttempt($responses);
        
        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
    }
    
        public function testIsRespondedMultipleInteractions1()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/is_responded/is_responded_multiple_interactions_singlechoice_textentry.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager());
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
                )
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
                )
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
                )
            ])
        );
        
        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
    }
    
    public function testIsRespondedMultipleInteractions2()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/is_responded/is_responded_multiple_interactions_multiplechoice_textentry.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent(), new SessionManager());
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
                )
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
                )
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
                )
            ])
        );
        
        $this->assertTrue($itemSession->isResponded());
        $this->assertTrue($itemSession->isResponded(false));
    }
}
