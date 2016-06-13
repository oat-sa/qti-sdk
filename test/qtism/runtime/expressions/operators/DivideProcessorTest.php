<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\QtiPoint;
use qtism\runtime\expressions\operators\DivideProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class DivideProcessorTest extends QtiSmTestCase {
	
	public function testDivide() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(1), new QtiInteger(1)));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(1, $result->getValue());
		
		$operands = new OperandsCollection(array(new QtiInteger(0), new QtiInteger(2)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands = new OperandsCollection(array(new QtiInteger(-30), new QtiInteger(5)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(-6, $result->getValue());
		
		$operands = new OperandsCollection(array(new QtiInteger(30), new QtiInteger(5)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(6, $result->getValue());
		
		$operands = new OperandsCollection(array(new QtiInteger(1), new QtiFloat(0.5)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(2, $result->getValue());
	}
	
	public function testDivisionByZero() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(1), new QtiInteger(0)));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testDivisionByInfinite() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiFloat(INF)));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands = new OperandsCollection(array(new QtiInteger(-1), new QtiFloat(INF)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(-0, $result->getValue());
	}
	
	public function testInfiniteDividedByInfinite() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiFloat(INF), new QtiFloat(INF)));
		$processor = new DivideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiString('string!'), new QtiBoolean(true)));
		$processor = new DivideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiPoint(1, 2), new QtiBoolean(true)));
		$processor = new DivideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new RecordContainer(array('A' => new QtiInteger(1))), new QtiInteger(10)));
		$processor = new DivideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new DivideProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiInteger(11), new QtiInteger(12)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
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
