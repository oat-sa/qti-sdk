<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentItemTestCase;
use qtism\common\datatypes\Identifier;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\SubmissionMode;
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

class AssessmentItemSessionTest extends QtiSmAssessmentItemTestCase {
	
    public function testInstantiation() {
        
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        // isPresented? isCorrect? isResponded? isSelected?
        $this->assertFalse($itemSession->isPresented());
        $this->assertFalse($itemSession->isCorrect());
        $this->assertFalse($itemSession->isResponded());
        $this->assertTrue($itemSession->isSelected());
        
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
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
        
        // Built-in variables instantiated and values initialized correctly?
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('numAttempts'));
        $this->assertInstanceOf('qtism\\common\\datatypes\\Integer', $itemSession['numAttempts']);
        $this->assertEquals(0, $itemSession['numAttempts']->getValue());
        
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $itemSession->getVariable('duration'));
        $this->assertInstanceOf('qtism\\common\\datatypes\\Duration', $itemSession['duration']);
        $this->assertEquals('PT0S', $itemSession['duration']->__toString());
        
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $itemSession->getVariable('completionStatus'));
        $this->assertInstanceOf('qtism\\common\\datatypes\\String', $itemSession['completionStatus']);
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
        $resp = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB'));
        $itemSession->endAttempt(new State(array($resp)));
        $this->assertTrue($itemSession->isResponded());
        
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
    
    public function testEvolutionAdaptiveItem() {
        $itemSession = self::instantiateBasicAdaptiveAssessmentItem();
        $itemSession->beginItemSession();
        
        // reminder, the value of maxAttempts is ignored when dealing with
        // adaptive items.
        
        // First candidate session, just give an incorrect response.
        // We do not known how much attempts to complete.
        $this->assertTrue($itemSession->isAttemptable());
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        $itemSession->beginAttempt();
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceE')))));
        $this->assertEquals(-1, $itemSession->getRemainingAttempts());
        
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertEquals('incomplete', $itemSession['completionStatus']->getValue());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
        
        $itemSession->beginAttempt();
        // Second attempt, give the correct answer to be allowed to go to the next item.
        $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());
        
        // If you now try to attempt again, exception because already completed.
        
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            // The session is closed, you cannot begin another attempt.
            $this->assertEquals(AssessmentItemSessionException::ATTEMPTS_OVERFLOW, $e->getCode());
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
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceD')));
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
        $state->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')));
        $itemSession->endAttempt($state);
        
        // Wrong answer ('ChoiceB' is the correct one), the session is not correct.
        $this->assertEquals('incomplete', $itemSession['completionStatus']->getValue());
        $this->assertFalse($itemSession->isCorrect());
        
        $state['RESPONSE'] = new Identifier('ChoiceB');
        $itemSession->beginAttempt();
        $itemSession->endAttempt($state);
        
        // Correct answer, the session is correct!
        $this->assertTrue($itemSession->isCorrect());
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
    }
    
    public function testStandaloneItemSession() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/hotspot.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('A'))));
        $itemSession->endAttempt($responses);
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $itemSession['SCORE']);
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());
    }
    
    public function testStandaloneMultipleInteractions() {
        $doc = new XmlDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/items/multiple_interactions.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSession->beginItemSession();
        $itemSession->beginAttempt();
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
        
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('Choice_3'))));
        $itemSession->endAttempt($responses);
        $this->assertEquals(6.0, $itemSession['SCORE']->getValue());
    }
    
    public function testModalFeedback() {
        $doc = new XmlDocument('2.1.0');
        $doc->load(self::samplesDir() . 'ims/items/2_1/modalFeedback.xml');
        
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setShowFeedback(true);
        $itemSessionControl->setMaxAttempts(0);
        $itemSession->setItemSessionControl($itemSessionControl);
        $itemSession->beginItemSession();
        
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('true'))));
        $itemSession->beginAttempt();
        $itemSession->endAttempt($responses);
        
        $this->assertEquals('correct', $itemSession['FEEDBACK']->getValue());
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $itemSession->getState());
        
        // new attempt!
        $responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('false'))));
        $itemSession->beginAttempt();
        $itemSession->endAttempt($responses);
        
        $this->assertEquals('incorrect', $itemSession['FEEDBACK']->getValue());
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $itemSession->getState());
        
        $itemSession->endItemSession();
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
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
    }
}