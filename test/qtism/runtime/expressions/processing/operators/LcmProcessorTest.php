<?php

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\LcmProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\common\OrderedContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;

class LcmProcessorTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider lcmProvider
	 * 
	 * @param array $operands
	 * @param integer $expected
	 */
	public function testLcm(array $operands, $expected) {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection($operands);
		$processor = new LcmProcessor($expression, $operands);
		$this->assertSame($expected, $processor->process());
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$processor = new LcmProcessor($expression, $operands);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::STRING, array('String!')), 10));
		$processor = new LcmProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 20, new RecordContainer(array('A' => 10)), 30));
		$processor = new LcmProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$result = $processor->process();
	}
	
	/**
	 * @dataProvider lcmWithNullValuesProvider
	 * 
	 * @param array $operands
	 */
	public function testGcdWithNullValues(array $operands) {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection($operands);
		$processor = new LcmProcessor($expression, $operands);
		$this->assertSame(null, $processor->process());
	}
	
	public function lcmProvider() {
		return array(
			array(array(0), 0),
			array(array(0, 0), 0),
			array(array(330, 0), 0),
			array(array(0, 330), 0),
			array(array(330, 0, 15), 0),
			array(array(330, 65, 15), 4290),
			array(array(-10, -5), 10),
			array(array(330), 330),
			array(array(330, new MultipleContainer(BaseType::INTEGER, array(65)), 15), 4290),
			array(array(new OrderedContainer(BaseType::INTEGER, array(330)), new MultipleContainer(BaseType::INTEGER, array(65)), new MultipleContainer(BaseType::INTEGER, array(15))), 4290),
			array(array(new OrderedContainer(BaseType::INTEGER, array(330, 65)), new MultipleContainer(BaseType::INTEGER, array(65))), 4290),
		);
	}
	
	public function lcmWithNullValuesProvider() {
		return array(
			array(array(null)),
			array(array(null, 10)),
			array(array(10, null)),
			array(array(10, null, 10)),
			array(array(10, new MultipleContainer(BaseType::INTEGER))),
			array(array(new OrderedContainer(BaseType::INTEGER, array(10, null))))
		);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<lcm>
				<baseValue baseType="integer">330</baseValue>
				<baseValue baseType="integer">65</baseValue>
				<baseValue baseType="integer">15</baseValue>
			</lcm>
		');
	}
}