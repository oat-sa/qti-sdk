<?php

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiIdentifier;

use qtism\common\datatypes\QtiFloat;

use qtism\common\datatypes\QtiInteger;

use qtism\common\datatypes\QtiString;

use qtism\runtime\common\RecordContainer;

use qtism\common\datatypes\QtiPoint;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

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
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('B'), new QtiString('C')));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		// [A,B,C] does not contain [C,B]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('C'), new QtiString('B')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
		
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('E'), new QtiString('F')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testPrimitiveOrderedLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B')));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] does not contain [B,A]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('B'), new QtiString('A')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testPrimitiveOrderedInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('B')));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('E')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
		
		// [A,B,C,D] contains [B,C]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')));
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('B'), new QtiString('C')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	}
	
	public function testPrimitiveMultipleTrailing() {
		$expression = $this->createFakeExpression();
	
		// For multiple containers [A,B,C] contains [B,C]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('B'), new QtiString('C')));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] contains [C,B]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('C'), new QtiString('B')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('E'), new QtiString('F')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testPrimitiveMultipleLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B')));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] contains [B,A]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('B'), new QtiString('A')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	}
	
	public function testPrimitiveMultipleInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('B')));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('E')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	
		// [A,B,C,D] contains [A,D]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('A'), new QtiString('D')));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	}

	public function testComplexOrderedTrailing() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [B,C]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] does not contain [C,B]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(5, 6), new QtiPoint(3, 4)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(7, 8), new QtiPoint(9, 10)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testComplexOrderedLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] does not contain [B,A]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(3, 4), new QtiPoint(1, 2)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testComplexOrderedInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(9, 10)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	
		// [A,B,C,D] contains [B,C]
		$operands->reset();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)));
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	}
	
	public function testComplexMultipleTrailing() {
		$expression = $this->createFakeExpression();
	
		// For multiple containers [A,B,C] contains [B,C]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] contains [C,B]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(5, 6), new QtiPoint(3, 4)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] does not contain [E,F]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(9, 10), new QtiPoint(11, 12)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testComplexMultipleLeading() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C] contains [A,B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C] contains [B,A]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(3, 4), new QtiPoint(1, 2)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		// [A,B,C] does not contain [B,D]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(3, 4), new QtiPoint(7, 8)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testComplexMultipleInBetween() {
		$expression = $this->createFakeExpression();
	
		// For ordered containers [A,B,C,D] contains [B]
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(3, 4)));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		// [A,B,C,D] does not contain [E]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(9, 10)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	
		// [A,B,C,D] contains [A,D]
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(7, 8)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(null, new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(25)))));
		$processor = new ContainsProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(25)))));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testNotSameBaseTypeOne() {
	    $expression = $this->createFakeExpression();
	    $operands = new OperandsCollection();
	    $operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('identifier3'), new QtiString('identifier4'), null, new QtiString('identifier2')));
	    $operands[] = new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier3'), new QtiIdentifier('identifier2')));
	    $processor = new ContainsProcessor($expression, $operands);
	    $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
	    $processor->process();
	}
	
	public function testNotSameBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(25)));
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(25.0)));
		$processor = new ContainsProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotSameCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(25)));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(25)));
		$processor = new ContainsProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(25)));
		$operands[] = new RecordContainer(array('1' => new QtiInteger(1), '2' => new QtiInteger(2)));
		$processor = new ContainsProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2)))));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new ContainsProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(25)));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(25)));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(25)));
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
