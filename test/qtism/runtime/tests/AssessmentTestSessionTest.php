<?php

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\common\State;
use qtism\data\NavigationMode;
use qtism\data\SubmissionMode;
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;
use qtism\data\state\VariableDeclaration;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\runtime\common\VariableIdentifier;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\common\datatypes\Point;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Pair;
use qtism\runtime\common\MultipleContainer;
use \OutOfBoundsException;

class AssessmentTestSessionTest extends QtiSmTestCase {
	
	protected $state;
	
	public function setUp() {
		parent::setUp();
		
		$xml = new XmlCompactAssessmentTestDocument('1.0');
		$xml->load(self::samplesDir() . 'custom/runtime/assessmenttest_context.xml');
		
		$this->state = AssessmentTestSession::instantiate($xml);
		$this->state['OUTCOME1'] = 'String!';
	}
	
	public function tearDown() {
	    parent::tearDown();
	    unset($this->state);
	}
	
	public function getState() {
		return $this->state;
	}
	
	public function testInstantiateOne() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());
	    
	    // You cannot get information on the current elements of 
	    // the test session when INITIAL state is in force.
	    $this->assertFalse($assessmentTestSession->getCurrentAssessmentItemRef());
	    $this->assertFalse($assessmentTestSession->getCurrentAssessmentSection());
	    $this->assertFalse($assessmentTestSession->getCurrentNavigationMode());
	    $this->assertFalse($assessmentTestSession->getCurrentSubmissionMode());
	    $this->assertFalse($assessmentTestSession->getCurrentTestPart());
	    $this->assertFalse($assessmentTestSession->getCurrentRemainingAttempts());

	    $assessmentTestSession->beginTestSession();
	    $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());
	    
	    // Now that the test session has begun, you can get information
	    // about the current elements of the session.
	    $this->assertEquals('P01', $assessmentTestSession->getCurrentTestPart()->getIdentifier());
	    $this->assertEquals('S01', $assessmentTestSession->getCurrentAssessmentSection()->getIdentifier());
	    $this->assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
	    $this->assertInternalType('integer', $assessmentTestSession->getCurrentNavigationMode());
	    $this->assertEquals(NavigationMode::LINEAR, $assessmentTestSession->getCurrentNavigationMode());
	    $this->assertInternalType('integer', $assessmentTestSession->getCurrentSubmissionMode());
	    $this->assertEquals(SubmissionMode::INDIVIDUAL, $assessmentTestSession->getCurrentSubmissionMode());
	    $this->assertEquals(1, $assessmentTestSession->getCurrentRemainingAttempts());
	    
	    // all outcome variables should have their default value set.
	    // all response variables should be set to NULL.
	    foreach ($doc->getComponentsByClassName('assessmentItemRef') as $itemRef) {
	        $score = $assessmentTestSession[$itemRef->getIdentifier() . '.SCORE'];
	        $this->assertInternalType('float', $score);
	        $this->assertEquals(0.0, $score);
	        
	        $response = $assessmentTestSession[$itemRef->getIdentifier() . '.RESPONSE'];
	        $this->assertSame(null, $response);
	    }
	    
	    // test-level outcome variables should be initialized
	    // with their default values.
	    $this->assertInternalType('float', $assessmentTestSession['SCORE']);
	    $this->assertEquals(0.0, $assessmentTestSession['SCORE']);
	}
	
	public function testInstantiateTwo() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	    // check Q01.1, Q01.2, Q01.3 item session initialization.
	    for ($i = 1; $i <= 3; $i++) {
	        $score = $assessmentTestSession["Q01.${i}.SCORE"];
	        $response = $assessmentTestSession["Q01.${i}.RESPONSE"];
	        $this->assertInternalType('float', $score);
	        $this->assertEquals(0.0, $score);
	        $this->assertSame(null, $response);
	    }
	}
	
	public function testSetVariableValuesAfterInstantiationOne() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
	     
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	    
	    // Change the value of the global SCORE.
	    $this->assertEquals(0.0, $assessmentTestSession['SCORE']);
	    $assessmentTestSession['SCORE'] = 20.0;
	    $this->assertEquals(20.0, $assessmentTestSession['SCORE']);
	    
	    // the assessment test session has no variable MAXSCORE.
	    $this->assertSame(null, $assessmentTestSession['MAXSCORE']);
	    try {
	        $assessmentTestSession['MAXSCORE'] = 20.0;
	        // An exception must be thrown in this case!
	        $this->assertTrue(false);
	    }
	    catch (OutOfBoundsException $e) {
	        $this->assertTrue(true);
	    }
	    
	    // Change the value of Q01.SCORE.
	    $this->assertEquals(0.0, $assessmentTestSession['Q01.SCORE']);
	    $assessmentTestSession['Q01.SCORE'] = 1.0;
	    $this->assertEquals(1.0, $assessmentTestSession['Q01.SCORE']);
	    
	    // Q01 has no 'MAXSCORE' variable.
	    $this->assertSame(null, $assessmentTestSession['Q01.MAXSCORE']);
	    try {
	        $assessmentTestSession['Q01.MAXSCORE'] = 1.0;
	        // An exception must be thrown !
	        $this->assertTrue(false);
	    }
	    catch (OutOfBoundsException $e) {
	        $this->assertTrue(true);
	    }
	    
	    // No item Q04.
	    $this->assertSame(null, $assessmentTestSession['Q04.SCORE']);
	    try {
	        $assessmentTestSession['Q04.SCORE'] = 1.0;
	        // Because no such item, outofbounds.
	        $this->assertTrue(false);
	    }
	    catch (OutOfBoundsException $e) {
	        $this->assertTrue(true);
	    }
	}
	
	public function testSetVariableValuesAfterInstantiationTwo() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');
	
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	     
	    // Change the value of Q01.2.SCORE.
	    $this->assertEquals(0.0, $assessmentTestSession['Q01.2.SCORE']);
	    $assessmentTestSession['Q01.2.SCORE'] = 1.0;
	    $this->assertEquals(1.0, $assessmentTestSession['Q01.2.SCORE']);
	    
	    // There is only 3 occurences of Q01. Try to go out of bounds.
	    try {
	        $assessmentTestSession['Q01.4.SCORE'] = 1.0;
	        // An OutOfBoundsException must be raised!
	        $this->assertTrue(false);  
	    }
	    catch (OutOfBoundsException $e) {
	        $this->assertTrue(true);
	    }
	}
	
	public function testLinearSkipAll() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	    
	    $this->assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
	    $this->assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
	    $this->assertEquals('S01', $assessmentTestSession->getCurrentAssessmentSection()->getIdentifier());
	    $this->assertEquals('P01', $assessmentTestSession->getCurrentTestPart()->getIdentifier());
	    
	    $assessmentTestSession->skip();
	    $this->assertEquals('Q02', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
	    $this->assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
	    
	    $this->assertEquals(1, $assessmentTestSession->getCurrentRemainingAttempts());
	    $assessmentTestSession->skip();
	    $this->assertEquals('Q03', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
	    $this->assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
	    
	    $assessmentTestSession->skip();
	    
	    $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
	    $this->assertFalse($assessmentTestSession->getCurrentAssessmentItemRef());
	    $this->assertFalse($assessmentTestSession->getCurrentAssessmentSection());
	    $this->assertFalse($assessmentTestSession->getCurrentTestPart());
	    $this->assertFalse($assessmentTestSession->getCurrentNavigationMode());
	    $this->assertFalse($assessmentTestSession->getCurrentSubmissionMode());
	}
	
	public function testLinearAnswerAll() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	    
	    // Q01 - Correct Response = 'ChoiceA'.
	    $this->assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
	    $assessmentTestSession->beginAttempt();
	    $responses = new State();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'));
	    $assessmentTestSession->endAttempt($responses);
	    $assessmentTestSession->moveNext();
	    
	    // Q02 - Correct Response = 'ChoiceB'.
	    $this->assertEquals('Q02', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
	    $assessmentTestSession->beginAttempt();
	    $responses = new State();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceC')); // -> incorrect x)
	    $assessmentTestSession->endAttempt($responses);
	    $assessmentTestSession->moveNext();
	    
	    // Q03 - Correct Response = 'ChoiceC'.
	    $this->assertEquals('Q03', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
	    $assessmentTestSession->beginAttempt();
	    $responses = new State();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceC'));
	    $assessmentTestSession->endAttempt($responses);
	    $assessmentTestSession->moveNext();
	    
	    // Check the final state of the test session.
	    // - Q01
	    $this->assertEquals('ChoiceA', $assessmentTestSession['Q01.RESPONSE']);
	    $this->assertInternalType('float', $assessmentTestSession['Q01.SCORE']);
	    $this->assertEquals(1.0, $assessmentTestSession['Q01.SCORE']);
	    $this->assertInternalType('integer', $assessmentTestSession['Q01.numAttempts']);
	    $this->assertEquals(1, $assessmentTestSession['Q01.numAttempts']);
	    
	    // - Q02
	    $this->assertEquals('ChoiceC', $assessmentTestSession['Q02.RESPONSE']);
	    $this->assertInternalType('float', $assessmentTestSession['Q02.SCORE']);
	    $this->assertEquals(0.0, $assessmentTestSession['Q02.SCORE']);
	    $this->assertInternalType('integer', $assessmentTestSession['Q02.numAttempts']);
	    $this->assertEquals(1, $assessmentTestSession['Q02.numAttempts']);
	    
	    // - Q03
	    $this->assertEquals('ChoiceC', $assessmentTestSession['Q03.RESPONSE']);
	    $this->assertInternalType('float', $assessmentTestSession['Q03.SCORE']);
	    $this->assertEquals(1.0, $assessmentTestSession['Q03.SCORE']);
	    $this->assertInternalType('integer', $assessmentTestSession['Q03.numAttempts']);
	    $this->assertEquals(1, $assessmentTestSession['Q03.numAttempts']);
	    
	    $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
	}
	
	/**
	 * @dataProvider linearOutcomeProcessingProvider
	 * 
	 * @param array $responses
	 * @param array $outcomes
	 */
	public function testLinearOutcomeProcessing(array $responses, array $outcomes) {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
	     
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	    
	    // There must be 8 outcome variables to be checked:
	    // NCORRECTS01, NCORRECTS02, NCORRECTS03, NINCORRECT, NRESPONDED
	    // NPRESENTED, NSELECTED, PERCENT_CORRECT.
	    $this->assertEquals(array_keys($outcomes), array('NCORRECTS01', 'NCORRECTS02', 'NCORRECTS03', 'NINCORRECT', 'NRESPONSED', 'NPRESENTED', 'NSELECTED', 'PERCENT_CORRECT'));
	    
	    // The selection of items for the test is 9.
	    $this->assertEquals(9, count($responses));
	    
	    foreach ($responses as $resp) {
	        $assessmentTestSession->beginAttempt();
	        $assessmentTestSession->endAttempt($resp);
	        $assessmentTestSession->moveNext();
	    }
	    
	    $this->assertFalse($assessmentTestSession->isRunning());
	    $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
	    
	    foreach ($outcomes as $outcomeIdentifier => $outcomeValue) {
	        $this->assertInternalType((is_int($outcomeValue)) ? 'integer' : 'float', $assessmentTestSession[$outcomeIdentifier]);
	        
	        if ($outcomeIdentifier !== 'PERCENT_CORRECT') {
	            $this->assertEquals($outcomeValue, $assessmentTestSession[$outcomeIdentifier]);
	        }
	        else {
	            $this->assertEquals(round($outcomeValue, 2), round($assessmentTestSession[$outcomeIdentifier], 2));
	        }
	    }
	}
	
	public function linearOutcomeProcessingProvider() {
	    $returnValue = array();
	    
	    // Test 1.
	    $outcomes = array('NCORRECTS01' => 2, 'NCORRECTS02' => 1, 'NCORRECTS03' => 1, 'NINCORRECT' => 5, 'NRESPONSED' => 9, 'NPRESENTED' => 9, 'NSELECTED' => 9, 'PERCENT_CORRECT' => 44.44);
	    $responses = array();
	    $responses['Q01'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'))); // SCORE = 1 - Correct
	    $responses['Q02'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'P'), new Pair('D', 'L')))))); // SCORE = 3 - Incorrect
	    $responses['Q03'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array('H', 'O'))))); // SCORE = 2 - Correct
	    $responses['Q04'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('W', 'Sp'), new DirectedPair('G2', 'Su')))))); // SCORE = 0 - Incorrect
	    $responses['Q05'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('C', 'B'), new Pair('C', 'D'), new Pair('B', 'D')))))); // SCORE = 1 - Incorrect
	    $responses['Q06'] = new State(array(new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, 'A'))); // SCORE = 1 - Correct
	    $responses['Q07.1'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(105, 105)))); // SCORE = 1 - Incorrect
	    $responses['Q07.2'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))); // SCORE = 1 - Correct
	    $responses['Q07.3'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(13, 37)))); // SCORE = 0 - Incorrect
	    
	    $test = array($responses, $outcomes);
	    $returnValue[] = $test;
	    
	    // Test 2 (full correct).
	    $outcomes = array('NCORRECTS01' => 3, 'NCORRECTS02' => 3, 'NCORRECTS03' => 3, 'NINCORRECT' => 0, 'NRESPONSED' => 9, 'NPRESENTED' => 9, 'NSELECTED' => 9, 'PERCENT_CORRECT' => 100.00);
	    $responses = array();
	    $responses['Q01'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'))); // SCORE = 1 - Correct
	    $responses['Q02'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'P'), new Pair('C', 'M'), new Pair('D', 'L')))))); // SCORE = 4 - Correct
	    $responses['Q03'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array('H', 'O'))))); // SCORE = 2 - Correct
	    $responses['Q04'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('W', 'G1'), new DirectedPair('Su', 'G2')))))); // SCORE = 3 - Correct
	    $responses['Q05'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('C', 'B'), new Pair('C', 'D')))))); // SCORE = 2 - Correct
	    $responses['Q06'] = new State(array(new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, 'A'))); // SCORE = 1 - Correct
	    $responses['Q07.1'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))); // SCORE = 1 - Correct
	    $responses['Q07.2'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))); // SCORE = 1 - Correct
	    $responses['Q07.3'] = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))); // SCORE = 0 - Correct
	     
	    $test = array($responses, $outcomes);
	    $returnValue[] = $test;
	    
	    return $returnValue;
	}
	
	public function testGetAssessmentItemSessions() {
	    // --- Test with single occurence items.
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	    
	    foreach (array('Q01', 'Q02', 'Q03') as $identifier) {
	        $sessions = $assessmentTestSession->getAssessmentItemSessions($identifier);
	        $this->assertEquals(1, count($sessions));
	        $this->assertEquals($identifier, $sessions[0]->getAssessmentItemRef()->getIdentifier());
	    }
	    
	    // Malformed $identifier.
	    try {
	        $sessions = $assessmentTestSession->getAssessmentItemSessions('Q04.1');
	        $this->assertFalse(true);
	    }
	    catch (InvalidArgumentException $e) {
	        $this->assertTrue(true);
	    }
	    
	    // Unknown assessmentItemRef.
	    $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
	    
	    // --- Test with multiple occurence items.
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $assessmentTestSession->beginTestSession();
	    
	    $sessions = $assessmentTestSession->getAssessmentItemSessions('Q01');
	    $this->assertEquals(3, count($sessions));
	    for ($i = 0; $i < count($sessions); $i++) {
	        $this->assertEquals('Q01', $sessions[$i]->getAssessmentItemRef()->getIdentifier());
	    }
	}
	
	/**
	 * @dataProvider getWeightProvider
	 * 
	 * @param string $identifier
	 * @param float $expectedValue
	 */
	public function testGetWeight($identifier, $expectedValue) {
		$state = $this->getState();
		
		$v = new VariableIdentifier($identifier);
		$weight = $state->getWeight($v);
		$this->assertInstanceOf('qtism\\data\\state\\Weight', $weight);
		$this->assertEquals($v->getVariableName(), $weight->getIdentifier());
		$this->assertEquals($expectedValue, $weight->getValue());
	}
	
	/**
	 * @dataProvider getWeightNotFoundProvider
	 * 
	 * @param string $identifier
	 */
	public function testGetWeightNotFound($identifier) {
		$state = $this->getState();
		
		$weight = $state->getWeight($identifier);
		$this->assertInternalType('boolean', $weight);
		$this->assertSame(false, $weight);
	}
	
	/**
	 * @dataProvider getWeightMalformed
	 * 
	 * @param string $identifier
	 */
	public function testGetWeightMalformed($identifier) {
	    $state = $this->getState();
	    $this->setExpectedException('\\InvalidArgumentException');
	    $state->getWeight($identifier);
	}
	
	public function getWeightProvider() {
		return array(
			array('Q01.W01', 1.0),
			array('Q01.W02', 1.1),
		    array('W01', 1.0),
		    array('W02', 1.1)
		);
	}
	
	public function getWeightNotFoundProvider() {
		return array(
			array('Q01.W03'),
			array('Q02.W02'),
		    array('Q01'),
		    array('W04')
		);
	}
	
	public function getWeightMalformed() {
	    return array(
	        array('_Q01'),
	        array('_Q01.SCORE'),
	        array('Q04.1.W01'),
	    );
	}
	
	public function testSelectionAndOrdering() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/selection_and_ordering_with_replacement.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(50, $assessmentTestSession->getRouteCount());
	}
	
	public function testOrderingBasic() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/ordering_basic.xml');
	     
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(3, $assessmentTestSession->getRouteCount());
	}
	
	public function testOrderingBasicFixed() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/ordering_basic_fixed.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(5, $assessmentTestSession->getRouteCount());
	}
    
	public function testOrderingVisible() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/ordering_visible.xml');
	     
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(9, $assessmentTestSession->getRouteCount());
	}
	
	public function testOrderingInvisibleDontKeepTogether() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/ordering_invisible_dont_keep_together.xml');
	
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(12, $assessmentTestSession->getRouteCount());
	}
	
	public function testOrderingInvisibleKeepTogether() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/ordering_invisible_keep_together.xml');
	
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(12, $assessmentTestSession->getRouteCount());
	}
}