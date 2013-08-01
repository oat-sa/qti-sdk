<?php
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;

use qtism\data\state\VariableDeclaration;

use qtism\data\state\OutcomeDeclarationCollection;

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
use \OutOfBoundsException;

class AssessmentTestContextTest extends QtiSmTestCase {
	
	protected $state;
	
	public function setUp() {
		parent::setUp();
		
		$xml = new XmlCompactAssessmentTestDocument('1.0');
		$xml->load(dirname(__FILE__) . '/../../../samples/custom/assessmenttest_context.xml');
		
		$this->state = new AssessmentTestContext($xml);
		$this->state['OUTCOME1'] = 'String!';
		$this->state['Q01.SCORE'] = 10;
		$this->state['Q01.MAXSCORE'] = 10.0;
	}
	
	public function getState() {
		return $this->state;
	}
	
	public function testGetVariableValue() {	
		$state = $this->getState();
		
		// get a value which is set (global scope).
		$val = $state['OUTCOME1'];
		$this->assertInternalType('string', $val);
		$this->assertEquals('String!', $val);
		
		// get a value which is set (item scope).
		$val = $state['Q01.SCORE'];
		$this->assertInternalType('integer', $val);
		$this->assertEquals(10, $val);
		
		// get a value which is not set (global score).
		$val = $state['OUTCOMEX'];
		$this->assertSame(null, $val);

		
		// get a value which is not set (item scope && item referenced).
		$val = $state['Q01.OVERKILL'];
		$this->assertSame(null, $val);
		
		
		// get a value which is not set (item scope && no such an item referenced).
		$val = $state['Q0X.OVERKILL'];
		$this->assertSame(null, $val);
	}
	
	public function testSetVariableValue() {
		$state = $this->getState();
		
		$state['OUTCOME1'] = 'New String!';
		$this->assertEquals($state['OUTCOME1'], 'New String!');
		
		$state['Q01.SCORE'] = 20;
		$this->assertEquals($state['Q01.SCORE'], 20);
		
		try {
			// The variable 'OUTCOME2' does not exist, it cannot be attributed a value.
			$state['OUTCOME2'] = 10;
			$this->assertTrue(false);
		}
		catch (OutOfBoundsException $e) {
			$this->assertTrue(true);
		}
		
		try {
			// The item 'Q01' is referenced but the 'OVERKILL' variable is not.
			$state['Q01.OVERKILL'] = 10;
			$this->assertTrue(false);	
		}
		catch (OutOfBoundsException $e) {
			$this->assertTrue(true);
		}
		
		try {
			// The item 'QOX' is not referenced.
			$state['Q0X.OVERKILL'] = 10;
			$this->assertTrue(false);
		}
		catch (OutOfBoundsException $e) {
			$this->assertTrue(true);
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
		
	public function testUnset() {
		$state = $this->getState();
		
		$this->assertEquals($state['OUTCOME1'], 'String!');
		unset($state['OUTCOME1']);
		$this->assertSame($state['OUTCOME1'], null);	
		
		$this->assertEquals($state['Q01.SCORE'], 10);
		unset($state['Q01.SCORE']);
		$this->assertSame($state['Q01.SCORE'], null);

		unset($state['XXX']);
		unset($state['Q01.X']);
		unset($state['Q02.X']);
	}
	
	public function testOffsetExists() {
		$state = $this->getState();
		$this->assertTrue(isset($state['OUTCOME1']));
		$this->assertFalse(isset($state['OUTCOMEX']));
		$this->assertTrue(isset($state['Q01.SCORE']));
		$this->assertFalse(isset($state['Q9999.SCORE']));
		$this->assertFalse(isset($state['Q01.X']));
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