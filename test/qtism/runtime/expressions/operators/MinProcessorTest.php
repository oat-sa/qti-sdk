<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiInteger;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\expressions\operators\MinProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\common\MultipleContainer;

class MinProcessorTest extends QtiSmTestCase {
	
	public function testWrongBaseType() {
		// As per QTI spec,
		// If any of the sub-expressions is NULL, the result is NULL.
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(-10);
		$operands[] = new QtiString('String');
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(10.0)));
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiFloat(-245.30);
		$rec =  new RecordContainer(); // will be at a first glance considered as NULL.
		$operands[] = $rec;
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$rec['A'] = new QtiInteger(1);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(10);
		$operands[] = new OrderedContainer(BaseType::FLOAT); // null
		$operands[] = new QtiFloat(-0.5);
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
		$operands = new OperandsCollection(array(new QtiInteger(-20), new QtiInteger(-10), new QtiInteger(0), new QtiInteger(10), new QtiInteger(20)));
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(-20, $result->getValue());
		
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(10002);
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(4566), new QtiInteger(8400), new QtiInteger(2094)));
		$operands[] = new QtiInteger(100002);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(2094, $result->getValue());
	}
	
	public function testMixed() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiFloat(26.4), new QtiInteger(-4), new QtiFloat(25.3)));
		$processor = new MinProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(-4.0, $result->getValue());
		
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(2), new QtiInteger(3), new QtiInteger(1), new QtiInteger(4), new QtiInteger(5)));
		$operands[] = new QtiFloat(2.4);
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(245.4), new QtiFloat(1337.1337)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(1.0, $result->getValue());
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
