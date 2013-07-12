<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Point;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\processing\OperandsCollection;
use qtism\runtime\expressions\processing\IsNullProcessor;

class IsNullProcessorTest extends QtiSmTestCase {
	
	public function testWithEmptyString() {
		$operands = new OperandsCollection();
		$operands[] = '';
		
		$expression = $this->getFakeExpression();
		$processor = new IsNullProcessor($expression, $operands);
		$this->assertTrue($processor->process());
	}
	
	public function testWithNull() {
		$operands = new OperandsCollection();
		$operands[] = null;
		
		$expression = $this->getFakeExpression();
		$processor = new IsNullProcessor($expression, $operands);
		$this->assertTrue($processor->process());
	}
	
	public function testEmptyContainers() {
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT);
		
		$expression = $this->getFakeExpression();
		$processor = new IsNullProcessor($expression, $operands);
		$this->assertTrue($processor->process());
		
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::BOOLEAN);
		$this->assertTrue($processor->process());
		
		$operands->reset();
		$operands[] = new RecordContainer();
		$this->assertTrue($processor->process());
	}
	
	public function testNotEmpty() {
		$expression = $this->getFakeExpression();
		$operands = new OperandsCollection(array(0));
		
		$processor = new IsNullProcessor($expression, $operands);
		$this->assertFalse($processor->process());
		
		$operands->reset();
		$operands[] = false;
		$this->assertFalse($processor->process());
		
		$operands->reset();
		$operands[] = -1;
		$this->assertFalse($processor->process());
		
		$operands->reset();
		$operands[] = new Point(1, 2);
		$this->assertFalse($processor->process());
		
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(25));
		$this->assertFalse($processor->process());
		
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(3, 4), new Point(5, 6)));
		$this->assertFalse($processor->process());
		
		$operands->reset();
		$operands[] = new RecordContainer(array('a' => true,  'b' => null,  'c' => new Point(1, 2), 'd' => 24, 'e' => 23.3));
		$this->assertFalse($processor->process());
	}
	
	public function testLessThanNeededOperands() {
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		
		$operands = new OperandsCollection();
		$expression = $this->getFakeExpression();
		$processor = new IsNullProcessor($expression, $operands);
		$result = $processor->process();
	}
	
	public function testMoreThanNeededOperands() {
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		
		$operands = new OperandsCollection(array(25, null));
		$expression = $this->getFakeExpression();
		$processor = new IsNullProcessor($expression, $operands);
		$result = $processor->process();
	}
	
	private function getFakeExpression() {
		$expression = $this->createComponentFromXml('
			<isNull>
				<baseValue baseType="string"></baseValue>
			</isNull>
		');
		
		return $expression;
	}
}