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
use \OutOfBoundsException;

class AssessmentTestSessionTest extends QtiSmTestCase {
	
	protected $state;
	
	public function setUp() {
		parent::setUp();
		
		$xml = new XmlCompactAssessmentTestDocument('1.0');
		$xml->load(dirname(__FILE__) . '/../../../samples/custom/assessmenttest_context.xml');
		
		$this->state = new AssessmentTestSession($xml);
		$this->state['OUTCOME1'] = 'String!';
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