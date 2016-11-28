<?php

use qtism\common\datatypes\QtiInteger;

use qtism\common\datatypes\QtiString;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\GcdProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\common\OrderedContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;

class GcdProcessorTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider gcdProvider
	 * 
	 * @param array $operands
	 * @param integer $expected
	 */
	public function testGcd(array $operands, $expected) {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection($operands);
		$processor = new GcdProcessor($expression, $operands);
		$this->assertSame($expected, $processor->process()->getValue());
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$processor = new GcdProcessor($expression, $operands);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::STRING, array(new QtiString('String!'))), new QtiInteger(10)));
		$processor = new GcdProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiInteger(20), new RecordContainer(array('A' => new QtiInteger(10))), new QtiInteger(30)));
		$processor = new GcdProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$result = $processor->process();
	}
	
	/**
	 * @dataProvider gcdWithNullValuesProvider
	 * 
	 * @param array $operands
	 */
	public function testGcdWithNullValues(array $operands) {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection($operands);
		$processor = new GcdProcessor($expression, $operands);
		$this->assertSame(null, $processor->process());
	}
	
	public function gcdProvider() {
		return array(
			array(array(new QtiInteger(45), new QtiInteger(60), new QtiInteger(330)), 15),
			array(array(new QtiInteger(0), new QtiInteger(45), new QtiInteger(60), new QtiInteger(0), new QtiInteger(330), new QtiInteger(15), new QtiInteger(0)), 15), // gcd (0, 45, 60, 330, 15, 0)
			array(array(new QtiInteger(0)), 0),
			array(array(new QtiInteger(0), new QtiInteger(0), new QtiInteger(0)), 0),
			array(array(new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(45), new QtiInteger(60), new QtiInteger(330)))), 15), // gcd(45, 60, 330)
			array(array(new QtiInteger(0), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(0)))), 0), // gcd(0, 0, 0)
			array(array(new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(45), new QtiInteger(60), new QtiInteger(0), new QtiInteger(330)))), 15), // gcd(45, 60, 0, 330)
			array(array(new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(45))), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(60))), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(330)))), 15),
			array(array(new QtiInteger(45)), 45),
			array(array(new QtiInteger(0), new QtiInteger(45)), 45),
			array(array(new QtiInteger(45), new QtiInteger(0)), 45),
			array(array(new QtiInteger(0), new QtiInteger(45), new QtiInteger(0)), 45)
		);
	}
	
	public function gcdWithNullValuesProvider() {
		return array(
			array(array(new QtiInteger(45), null, new QtiInteger(330))),
			array(array(new QtiString(''), new QtiInteger(550), new QtiInteger(330))),
			array(array(new QtiInteger(230), new OrderedContainer(BaseType::INTEGER), new QtiInteger(25), new QtiInteger(33))),
			array(array(new OrderedContainer(BaseType::INTEGER, array(null)))),
			array(array(new OrderedContainer(BaseType::INTEGER, array(null, null, null)))),
			array(array(new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(25), new QtiInteger(30))), new QtiInteger(200), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(25), null, new QtiInteger(30)))))
		);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<gcd>
				<baseValue baseType="integer">40</baseValue>
				<baseValue baseType="integer">60</baseValue>
				<baseValue baseType="integer">330</baseValue>
			</gcd>
		');
	}
}
