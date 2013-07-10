<?php

use qtism\data\state\ResponseDeclaration;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\OutcomeVariable;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

class StateTest extends QtiSmTestCase {

	public function testInstantiation() {
		$state = new State();
		$this->assertInstanceOf('qtism\\runtime\\common\\State', $state);
		$this->assertEquals(0, count($state));
		
		$varsArray = array();
		$varsArray[] = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER);
		$varsArray[] = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
		
		$state = new State($varsArray);
		$this->assertEquals(2, count($state));
		$this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $state->getVariable('RESPONSE'));
		$this->assertEquals($state->getVariable('RESPONSE')->getBaseType(), BaseType::INTEGER);
		
		// replace a variable.
		$var = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FLOAT);
		$state->setVariable($var);
		$this->assertEquals($state->getVariable('RESPONSE')->getBaseType(), BaseType::FLOAT);
		
		// unset a variable.
		unset($state['RESPONSE']);
		$isset = isset($state['RESPONSE']);
		$this->assertFalse($isset);
		$this->assertTrue($state['RESPONSE'] === null);
	}
	
	public function testInstantiationInvalid() {
		$this->setExpectedException('\\InvalidArgumentException');
		$state = new State(array(15, 'string', new \stdClass()));
	}
	
	public function testAddressing() {
		$state = new State();
		$response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER);
		$score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
		
		$state->setVariable($response);
		$state->setVariable($score);
		
		$this->assertTrue($state['foo'] === null);
		$this->assertTrue($response === $state->getVariable('RESPONSE'));
		$this->assertTrue($score === $state->getVariable('SCORE'));
		$this->assertTrue(isset($state['SCORE']));
		$this->assertFalse(isset($state['SCOREX']));
	}
	
	public function testAddressingInvalidOne() {
		$this->setExpectedException('\\OutOfRangeException');
		$state = new State();
		$state['var'] = new ResponseDeclaration('var', BaseType::POINT, Cardinality::ORDERED);
	}
	
	public function testAdressingInvalidTwo() {
		$this->setExpectedException('\\OutOfRangeException');
		$state = new State();
		$var = $state[3];
	}
}