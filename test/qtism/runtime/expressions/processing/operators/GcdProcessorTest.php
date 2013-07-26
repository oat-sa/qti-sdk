<?php

use qtism\runtime\common\RecordContainer;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\processing\operators\GcdProcessor;
use qtism\runtime\expressions\processing\operators\OperandsCollection;
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
		$this->assertSame($expected, $processor->process());
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\operators\\OperatorProcessingException');
		$processor = new GcdProcessor($expression, $operands);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::STRING, array('String!')), 10));
		$processor = new GcdProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\operators\\OperatorProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 20, new RecordContainer(array('A' => 10)), 30));
		$processor = new GcdProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\operators\\OperatorProcessingException');
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
			array(array(45, 60, 330), 15),
			array(array(0, 45, 60, 0, 330, 15, 0), 15), // gcd (0, 45, 60, 330, 15, 0)
			array(array(0), 0),
			array(array(0, 0, 0), 0),
			array(array(new MultipleContainer(BaseType::INTEGER, array(45, 60, 330))), 15), // gcd(45, 60, 330)
			array(array(0, new OrderedContainer(BaseType::INTEGER, array(0))), 0), // gcd(0, 0, 0)
			array(array(new MultipleContainer(BaseType::INTEGER, array(45, 60, 0, 330))), 15), // gcd(45, 60, 0, 330)
			array(array(new MultipleContainer(BaseType::INTEGER, array(45)), new OrderedContainer(BaseType::INTEGER, array(60)), new MultipleContainer(BaseType::INTEGER, array(330))), 15),
			array(array(45), 45),
			array(array(0, 45), 45),
			array(array(45, 0), 45),
			array(array(0, 45, 0), 45)
		);
	}
	
	public function gcdWithNullValuesProvider() {
		return array(
			array(array(45, null, 330)),
			array(array('', 550, 330)),
			array(array(230, new OrderedContainer(BaseType::INTEGER), 25, 33)),
			array(array(new OrderedContainer(BaseType::INTEGER, array(null)))),
			array(array(new OrderedContainer(BaseType::INTEGER, array(null, null, null)))),
			array(array(new OrderedContainer(BaseType::INTEGER, array(25, 30)), 200, new MultipleContainer(BaseType::INTEGER, array(25, null, 30))))
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