<?php
use qtism\runtime\common\RecordContainer;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Point;
use qtism\runtime\expressions\processing\operators\DivideProcessor;
use qtism\runtime\expressions\processing\operators\OperandsCollection;

class DivideProcessorTest extends QtiSmTestCase {
	
	public function testDivide() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(1, 1));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1, $result);
		
		$operands = new OperandsCollection(array(0, 2));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(0, $result);
		
		$operands = new OperandsCollection(array(-30, 5));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(-6, $result);
		
		$operands = new OperandsCollection(array(30, 5));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(6, $result);
		
		$operands = new OperandsCollection(array(1, 0.5));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(2, $result);
	}
	
	public function testDivisionByZero() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(1, 0));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testDivisionByInfinite() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(1, INF));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(0, $result);
		
		$operands = new OperandsCollection(array(-1, INF));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(-0, $result);
	}
	
	public function testInfiniteDividedByInfinite() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(INF, INF));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('string!', true));
		$processor = new DivideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new Point(1, 2), true));
		$processor = new DivideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new RecordContainer(array('A' => 1)), 10));
		$processor = new DivideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new DivideProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 11, 12));
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new DivideProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<divide>
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="integer">2</baseValue>
			</divide>
		');
	}
}