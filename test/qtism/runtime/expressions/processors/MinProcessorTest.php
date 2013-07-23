<?php
use qtism\runtime\common\OrderedContainer;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\common\RecordContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\expressions\processing\MinProcessor;
use qtism\runtime\expressions\processing\OperandsCollection;
use qtism\runtime\common\MultipleContainer;

class MinProcessorTest extends QtiSmTestCase {
	
	public function testWrongBaseType() {
		// As per QTI spec,
		// If any of the sub-expressions is NULL, the result is NULL.
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = -10;
		$operands[] = 'String';
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(10.0));
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = -245.30;
		$rec =  new RecordContainer(); // will be at a first glance considered as NULL.
		$operands[] = $rec;
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$rec['A'] = 1;
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10;
		$operands[] = new OrderedContainer(BaseType::FLOAT); // null
		$operands[] = -0.5;
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands = new OperandsCollection(array(null));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testAllIntegers() {
		// As per QTI spec,
		// if all sub-expressions are of integer type, a single integer (ndlr: is returned).
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(-20, -10, 0, 10, 20));
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(-20, $result);
		
		$operands = new OperandsCollection();
		$operands[] = 10002;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(4566, 8400, 2094));
		$operands[] = 100002;
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(2094, $result);
	}
	
	public function testMixed() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 26.4, -4, 25.3));
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(-4.0, $result);
		
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(2, 3, 1, 4, 5));
		$operands[] = 2.4;
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(245.4, 1337.1337));
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1.0, $result);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<min>
				<baseValue baseType="float">25.4</baseValue>
				<baseValue baseType="integer">25</baseValue>
				<multiple>
					<baseValue baseType="integer">100</baseValue>
					<baseValue baseType="integer">150</baseValue>
					<baseValue baseType="integer">200</baseValue>
				</multiple>
			</min>
		');
	}
}