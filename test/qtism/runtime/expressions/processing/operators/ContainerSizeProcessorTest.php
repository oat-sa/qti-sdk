<?php

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\processing\operators\ContainerSizeProcessor;
use qtism\data\expressions\operators\ContainerSize; 
use qtism\runtime\expressions\processing\operators\OperandsCollection;
use qtism\common\datatypes\Point;
use qtism\runtime\common\RecordContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;

class ContainerSizeProcessorTest extends QtiSmTestCase {
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new ContainerSizeProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(25));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(26));
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new ContainerSizeProcessor($expression, $operands);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(null));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertSame(0, $result);
		
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertSame(0, $result);
	}
	
	public function testWrongCardinalityOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(25));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new RecordContainer(array('1' => 1.0, '2' => 2))));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testSize() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array('String!'));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEquals(1, $result);
		
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(2, 3), new Point(3, 4)));
		$result = $processor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(3, $result);;
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<containerSize>
				<multiple>
					<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">2</baseValue>
				<baseValue baseType="integer">3</baseValue>
				</multiple>
			</containerSize>
		');
	}
}