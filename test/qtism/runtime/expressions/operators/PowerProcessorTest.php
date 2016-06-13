<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\PowerProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class PowerProcessorTest extends QtiSmTestCase {
	
	public function testPowerNormal() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(0), new QtiInteger(0)));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(1, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(256);
		$operands[] = new QtiInteger(0);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(1, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(0);
		$operands[] = new QtiInteger(0);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(1, $result->getValue());

		$operands->reset();
		$operands[] = new QtiInteger(0);
		$operands[] = new QtiInteger(2);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(2);
		$operands[] = new QtiInteger(8);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(256, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(20);
		$operands[] = new QtiFloat(3.4);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(26515, intval($result->getValue()));
	}
	
	public function testOverflow() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(2), new QtiInteger(100000000)));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testUnderflow() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(-2), new QtiInteger(333333333)));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testInfinite() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiFloat(INF), new QtiFloat(INF)));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertTrue(is_infinite($result->getValue()));
	}
	
	public function testNull() {
		// exp as a float is NaN when negative base is used.
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(-20), new QtiFloat(3.4)));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = new QtiInteger(1);
		$operands[] = null;
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::FLOAT);
		$operands[] = new QtiInteger(2);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(-20), new QtiString('String!')));
		$processor = new PowerProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(-20), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(10)))));
		$processor = new PowerProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(-20)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new PowerProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(-20), new QtiInteger(20), new QtiInteger(30)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new PowerProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<power>
				<baseValue baseType="integer">2</baseValue>
				<baseValue baseType="integer">8</baseValue>
			</power>
		');
	}
}
