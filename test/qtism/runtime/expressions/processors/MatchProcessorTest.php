<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
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
	
	public function testDifferentBaseTypesScalar() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 15;
		$operands[] = 'String!';
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentBaseTypesContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(10, 20, 30, 40));
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(10.0, 20.0, 30.0, 40.0));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentBaseTypesMixed() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 'String!';
		$operands[] = new OrderedContainer(BaseType::FLOAT, array(10.0, 20.0));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentCardinalitiesOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 'String!';
		$operands[] = new MultipleContainer(BaseType::STRING, array('String!'));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentCardinalitiesTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array('String!'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('String!'));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentCardinalitiesThree() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array('String!'));
		$operands[] = new RecordContainer(array('entry1' => 'String!'));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(15));
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new MatchProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(25, 25, 25));
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new MatchProcessor($expression, $operands);
	}
	
	public function testNullScalar() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(15.0, null));
		$processor = new MatchProcessor($expression, $operands);
		$this->assertSame(null, $processor->process());
	}
	
	public function testNullContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(10, 20));
		$operands[] = new MultipleContainer(BaseType::INTEGER);
		$processor = new MatchProcessor($expression, $operands);
		$this->assertSame(null, $processor->process());
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