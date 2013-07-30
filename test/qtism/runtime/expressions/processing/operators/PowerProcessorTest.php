<?php

use qtism\common\enums\BaseType;

use qtism\runtime\common\MultipleContainer;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\operators\PowerProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class PowerProcessorTest extends QtiSmTestCase {
	
	public function testPowerNormal() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(0, 0));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1, $result);
		
		$operands->reset();
		$operands[] = 256;
		$operands[] = 0;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1, $result);
		
		$operands->reset();
		$operands[] = 0;
		$operands[] = 0;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1, $result);

		$operands->reset();
		$operands[] = 0;
		$operands[] = 2;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(0, $result);
		
		$operands->reset();
		$operands[] = 2;
		$operands[] = 8;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(256, $result);
		
		$operands->reset();
		$operands[] = 20;
		$operands[] = 3.4;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(26515, intval($result));
	}
	
	public function testOverflow() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(2, 100000000));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testUnderflow() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(-2, 333333333));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testInfinite() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(INF, INF));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertTrue(is_infinite($result));
	}
	
	public function testNull() {
		// exp as a float is NaN when negative base is used.
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(-20, 3.4));
		$processor = new PowerProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = 1;
		$operands[] = null;
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::FLOAT);
		$operands[] = 2;
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(-20, 'String!'));
		$processor = new PowerProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(-20, new MultipleContainer(BaseType::INTEGER, array(10))));
		$processor = new PowerProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(-20));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new PowerProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(-20, 20, 30));
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