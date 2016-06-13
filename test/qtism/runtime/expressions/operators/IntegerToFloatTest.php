<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\IntegerToFloatProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class IntegerToFloatProcessorTest extends QtiSmTestCase {
	
	public function testIntegerToFloat() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(10);
		$processor = new IntegerToFloatProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(10.0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(-10);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(-10.0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(0);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(0.0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(-0);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(-0.0, $result->getValue());
	}
	
	public function testNullOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = null;
		$processor = new IntegerToFloatProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testNullTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiString('');
		$processor = new IntegerToFloatProcessor($expression, $operands);
	
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(1), new QtiInteger(2), new QtiInteger(3)));
		$processor = new IntegerToFloatProcessor($expression, $operands);
		
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiString('String!');
		$processor = new IntegerToFloatProcessor($expression, $operands);
		
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPoint(1, 2);
		$processor = new IntegerToFloatProcessor($expression, $operands);
	
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new IntegerToFloatProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(10);
		$operands[] = new QtiInteger(-10);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new IntegerToFloatProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<integerToFloat>
				<baseValue baseType="integer">1337</baseValue>
			</integerToFloat>
		');
	}
}
