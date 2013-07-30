<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\common\datatypes\Point;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\SubtractProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class SubtractProcessorTest extends QtiSmTestCase {
	
	public function testSubtract() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 256));
		$processor = new SubtractProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(-246, $result);
		
		$operands = new OperandsCollection(array(-5.0, -10));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(5, $result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, null));
		$processor = new SubtractProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::FLOAT);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, new Point(1, 2)));
		$processor = new SubtractProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER, array(10)), 20));
		$processor = new SubtractProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new SubtractProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 20, 30, 40));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new SubtractProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<subtract>
				<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">1</baseValue>
			</subtract>
		');
	}
}