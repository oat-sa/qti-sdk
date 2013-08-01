<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\common\VariableIdentifier;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\tests\AssessmentTestContext;

class AssessmentTestContextTest extends QtiSmTestCase {
	
	protected $state;
	
	public function setUp() {
		parent::setUp();
		
		// AssessmentItemRefs involved.
		$itemArray = array();
		
		$assessmentItemRef = new AssessmentItemRef('Q01', './Q01.xml');
		$assessmentItemRef->setWeights(new WeightCollection(array(new Weight('W01', 1.0), new Weight('W02', 1.1))));
		$itemArray[] = $assessmentItemRef;
		
		$variables = array();
		$variables[] = new OutcomeVariable('Q01.SCORE', Cardinality::SINGLE, BaseType::INTEGER, 10);
		$variables[] = new OutcomeVariable('Q01.MAXSCORE', Cardinality::SINGLE, BaseType::FLOAT, 10.0);
		
		$this->state = new AssessmentTestContext($variables, new AssessmentItemRefCollection($itemArray));
	}
	
	public function getState() {
		return $this->state;
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