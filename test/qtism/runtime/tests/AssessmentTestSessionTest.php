<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

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
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionState;
use \OutOfBoundsException;

class AssessmentTestSessionTest extends QtiSmTestCase {
	
	protected $state;
	
	public function setUp() {
		parent::setUp();
		
		$xml = new XmlCompactAssessmentTestDocument('1.0');
		$xml->load(self::samplesDir() . 'custom/runtime/assessmenttest_context.xml');
		
		$this->state = AssessmentTestSession::instantiate($xml);
		$this->state['OUTCOME1'] = 'String!';
		ini_set('max_execution_time', 1);
	}
	
	public function getState() {
		return $this->state;
	}
	
	public function testInstantiateOne() {
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
	    
	    $assessmentTestSession = AssessmentTestSession::instantiate($doc);
	    $this->assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());

	    $assessmentTestSession->beginTestSession();
	    $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());
	    
	    // all outcome variables should have their default value set.
	    // all response variables should be set to NULL.
	    foreach ($doc->getComponentsByClassName('assessmentItemRef') as $itemRef) {
	        $score = $assessmentTestSession[$itemRef->getIdentifier() . '.SCORE'];
	        $this->assertInternalType('float', $score);
	        $this->assertEquals(0.0, $score);
	        
	        $response = $assessmentTestSession[$itemRef->getIdentifier() . '.RESPONSE'];
	        $this->assertSame(null, $response);
	    }
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
	 * @dataProvider getWeightProviderNoPrefix
	 * 
	 * @param string $identifier
	 */
	public function testGetWeightBadPrefix($identifier) {
		$state = $this->getState();
		
		$this->setExpectedException('\\InvalidArgumentException');
		$weight = $state->getWeight($identifier);
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
	
	public function getWeightProvider() {
		return array(
			array('Q01.W01', 1.0),
			array('Q01.W02', 1.1)
		);
	}
	
	public function getWeightProviderNoPrefix() {
		return array(
			array('W01'),
			array('W02'),
			array('Q01'),
			array('_Q01.W01') // malformed
		);
	}
	
	public function getWeightNotFoundProvider() {
		return array(
			array('Q01.W03'),
			array('Q02.W02')
		);
	}
}