<?php

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Duration;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\IntegerModulusProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class IntegerModulusProcessorTest extends QtiSmTestCase {
	
	public function testIntegerModulus() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 5));
		$processor = new IntegerModulusProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(0, $result);
		
		$operands = new OperandsCollection(array(49, -5));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(4, $result);
		
		$operands = new OperandsCollection(array(36, 7));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(1, $result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(null, 5));
		$processor = new IntegerModulusProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testModulusByZero() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(50, 0));
		$processor = new IntegerModulusProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER, array(10)), 5));
		$processor = new IntegerModulusProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('ping!', 5));
		$processor = new IntegerModulusProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(5, new Duration('P1D')));
		$processor = new IntegerModulusProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(5));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new IntegerModulusProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(5, 5, 5));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new IntegerModulusProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<integerModulus>
				<baseValue baseType="integer">36</baseValue>
				<baseValue baseType="integer">7</baseValue>
			</integerModulus>
		');
	}
}