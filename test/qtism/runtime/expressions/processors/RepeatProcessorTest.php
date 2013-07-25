<?php

use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\processing\RepeatProcessor;
use qtism\runtime\expressions\processing\OperandsCollection;

class RepeatProcessorTest extends QtiSmTestCase {
	
	public function testRepeatScalarOnly() {
		$initialVal = array(1, 2, 3);
		$expression = $this->createFakeExpression(1);
		$operands = new OperandsCollection($initialVal);
		$processor = new RepeatProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertTrue($result->equals(new OrderedContainer(BaseType::INTEGER, $initialVal)));
		
		$expression = $this->createFakeExpression(2);
		$processor->setExpression($expression);
		$result = $processor->process();
		$this->assertTrue($result->equals(new OrderedContainer(BaseType::INTEGER, array_merge($initialVal, $initialVal))));
	}
	
	public function testOrderedOnly() {
		$expression = $this->createFakeExpression(2);
		$ordered1 = new OrderedContainer(BaseType::INTEGER, array(1, 2, 3));
		$ordered2 = new OrderedContainer(BaseType::INTEGER, array(4));
		$operands = new OperandsCollection(array($ordered1, $ordered2));
		$processor = new RepeatProcessor($expression, $operands);
		$result = $processor->process();
		
		$comparison = new OrderedContainer(BaseType::INTEGER, array(1, 2, 3, 4, 1, 2, 3, 4));
		$this->assertTrue($comparison->equals($result));
	}
	
	public function testMixed() {
		
	}
	
	public function createFakeExpression($numberRepeats = 1) {
		return $this->createComponentFromXml('
			<repeat numberRepeats="' . $numberRepeats . '">
				<baseValue baseType="integer">120</baseValue>
			</repeat>
		');
	}
}