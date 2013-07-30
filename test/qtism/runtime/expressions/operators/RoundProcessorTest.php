<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Duration;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\RoundProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class RoundProcessorTest extends QtiSmTestCase {
	
	public function testRound() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 6.8;
		$processor = new RoundProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(7, $result);
		
		$operands->reset();
		$operands[] = 6.5;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(7, $result);
		
		$operands->reset();
		$operands[] = 6.49;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(6, $result);
		
		$operands->reset();
		$operands[] = 6.5;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(7, $result);
		
		$operands->reset();
		$operands[] = -6.5;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(-6, $result);
		
		$operands->reset();
		$operands[] = -6.51;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(-7, $result);
		
		$operands->reset();
		$operands[] = -6.49;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(-6, $result);
		
		$operands->reset();
		$operands[] = 0;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(0, $result);
		
		$operands->reset();
		$operands[] = -0.0;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(0, $result);
		
		$operands->reset();
		$operands[] = -0.5;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(0, $result);
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
		$operands[] = new OrderedContainer(BaseType::FLOAT, array(1.1, 2.2));
		$processor = new RoundProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = true;
		$processor = new RoundProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Duration('P1D');
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
		$operands[] = 10;
		$operands[] = 1.1;
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