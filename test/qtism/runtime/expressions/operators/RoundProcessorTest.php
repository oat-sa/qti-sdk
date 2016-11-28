<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiDuration;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\RoundProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class RoundProcessorTest extends QtiSmTestCase {
	
	public function testRound() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiFloat(6.8);
		$processor = new RoundProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(7, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(6.5);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(7, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(6.49);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(6.5);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(7, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-6.5);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(-6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-6.51);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(-7, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-6.49);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(-6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(0);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-0.0);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-0.5);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(0, $result->getValue());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = null;
		$processor = new RoundProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(1.1), new QtiFloat(2.2)));
		$processor = new RoundProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiBoolean(true);
		$processor = new RoundProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiDuration('P1D');
		$processor = new RoundProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new RoundProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(10);
		$operands[] = new QtiFloat(1.1);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new RoundProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<round>
				<baseValue baseType="float">6.49</baseValue>
			</round>
		');
	}
}
