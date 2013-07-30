<?php

use qtism\runtime\common\RecordContainer;

use qtism\common\datatypes\Point;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\ContainsProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class ContainsProcessorTest extends QtiSmTestCase {
	
	public function testPrimitiveOrderedTrailing() {
		$expression = $this->createFakeExpression();
		
		// For ordered containers [A,B,C] contains [B,C]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('B', 'C'));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		// [A,B,C] does not contain [C,B]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('C', 'B'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
		
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('E', 'F'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testPrimitiveOrderedLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B'));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] does not contain [B,A]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('B', 'A'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testPrimitiveOrderedInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C', 'D'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('B'));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C', 'D'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('E'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
		
		// [A,B,C,D] contains [B,C]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array('A', 'B', 'C', 'D'));
		$operands[] = new OrderedContainer(BaseType::STRING, array('B', 'C'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	}
	
	public function testPrimitiveMultipleTrailing() {
		$expression = $this->createFakeExpression();
	
		// For multiple containers [A,B,C] contains [B,C]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('B', 'C'));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] contains [C,B]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('C', 'B'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('E', 'F'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testPrimitiveMultipleLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B'));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] contains [B,A]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('B', 'A'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	}
	
	public function testPrimitiveMultipleInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C', 'D'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('B'));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C', 'D'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('E'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	
		// [A,B,C,D] contains [A,D]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'B', 'C', 'D'));
		$operands[] = new MultipleContainer(BaseType::STRING, array('A', 'D'));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	}

	public function testComplexOrderedTrailing() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [B,C]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(3, 4), new Point(5, 6)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] does not contain [C,B]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(5, 6), new Point(3, 4)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(7, 8), new Point(9, 10)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testComplexOrderedLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] does not contain [B,A]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(3, 4), new Point(1, 2)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testComplexOrderedInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6), new Point(7, 8)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6), new Point(7, 8)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(9, 10)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	
		// [A,B,C,D] contains [B,C]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6), new Point(7, 8)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(3, 4), new Point(5, 6)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	}
	
	public function testComplexMultipleTrailing() {
		$expression = $this->createFakeExpression();
	
		// For multiple containers [A,B,C] contains [B,C]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(3, 4), new Point(5, 6)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] contains [C,B]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(5, 6), new Point(3, 4)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(9, 10), new Point(11, 12)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testComplexMultipleLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C] contains [B,A]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(3, 4), new Point(1, 2)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		// [A,B,C] does not contain [B,D]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(3, 4), new Point(7, 8)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testComplexMultipleInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6), new Point(7, 8)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6), new Point(7, 8)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(9, 10)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	
		// [A,B,C,D] contains [A,D]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(3, 4), new Point(5, 6), new Point(7, 8)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2), new Point(7, 8)));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(null, new MultipleContainer(BaseType::INTEGER, array(25))));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER), new MultipleContainer(BaseType::INTEGER, array(25))));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testNotSameBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(25));
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(25.0));
		$processor = new ContainsProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotSameCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(25));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(25));
		$processor = new ContainsProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(25));
		$operands[] = new RecordContainer(array('1' => 1, '2' => 2));
		$processor = new ContainsProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::POINT, array(new Point(1, 2)))));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new ContainsProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(25));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(25));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(25));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new ContainsProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<contains>
				<multiple>
					<baseValue baseType="string">A</baseValue>
					<baseValue baseType="string">B</baseValue>
					<baseValue baseType="string">C</baseValue>
				</multiple>
				<multiple>
					<baseValue baseType="string">B</baseValue>
					<baseValue baseType="string">C</baseValue>
				</multiple>
			</contains>
		');
	}
}