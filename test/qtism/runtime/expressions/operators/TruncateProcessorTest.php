<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Duration;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\TruncateProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class TruncateProcessorTest extends QtiSmTestCase {
	
	public function testRound() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 6.8;
		$processor = new TruncateProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(6, $result);
		
		$operands->reset();
		$operands[] = 6.5;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(6, $result);
		
		$operands->reset();
		$operands[] = 6.49;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(6, $result);
		
		$operands->reset();
		$operands[] = -6.5;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(-6, $result);
		
		$operands->reset();
		$operands[] = -6.8;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(-6, $result);
		
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
		
		$operands->reset();
		$operands[] = -0.4;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(0, $result);
		
		$operands->reset();
		$operands[] = -0.6;
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(0, $result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = null;
		$processor = new TruncateProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::FLOAT, array(1.1, 2.2));
		$processor = new TruncateProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = true;
		$processor = new TruncateProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Duration('P1D');
		$processor = new TruncateProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new TruncateProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10;
		$operands[] = 1.1;
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new TruncateProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<truncate>
				<baseValue baseType="float">6.49</baseValue>
			</truncate>
		');
	}
}