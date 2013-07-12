<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\processing\OperandsCollection;
use qtism\runtime\expressions\processing\MatchProcessor;

class MatchProcessorTest extends QtiSmTestCase {
	
	public function testScalar() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(10, 10));
		$processor = new MatchProcessor($expression, $operands);
		$this->assertTrue($processor->process() === true);
		
		$operands = new OperandsCollection(array(10, 11));
		$processor->setOperands($operands);
		$this->assertFalse($processor->process() === true);
	}
	
	public function testContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(5, 4, 3, 2, 1));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(1, 2, 3, 4, 5));
		$processor = new MatchProcessor($expression, $operands);
		
		$this->assertTrue($processor->process() === true);
		
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(5, 4, 3, 2, 1));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(1, 6, 7, 8, 5));
		$processor->setOperands($operands);
		$this->assertFalse($processor->process() === true);
	}
	
	private function createFakeExpression() {
		return $this->createComponentFromXml('
			<match>
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="integer">11</baseValue>
			</match>
		');
	}
}