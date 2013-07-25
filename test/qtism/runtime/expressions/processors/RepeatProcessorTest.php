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
	
	public function createFakeExpression($numberRepeats = 1) {
		return $this->createComponentFromXml('
			<repeat numberRepeats="' . $numberRepeats . '">
				<baseValue baseType="integer">120</baseValue>
			</repeat>
		');
	}
}