<?php
require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Pair;
use qtism\common\datatypes\Point;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\MemberProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class MemberProcessorTest extends QtiSmTestCase {
	
	public function testMultiple() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10.1;
		$mult = new MultipleContainer(BaseType::FLOAT, array(1.1, 2.1, 3.1));
		$operands[] = $mult;
		$processor = new MemberProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertEquals(false, $result);
		
		$mult[] = 10.1;
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertEquals(true, $result);
	}
	
	public function testOrdered() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Pair('A', 'B');
		$ordered = new OrderedContainer(BaseType::PAIR, array(new Pair('B', 'C')));
		$operands[] = $ordered;
		$processor = new MemberProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertEquals(false, $result);
		
		$ordered[] = new Pair('A', 'B');
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertEquals(true, $result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		
		// second operand is null.
		$operands[] = 10;
		$operands[] = new OrderedContainer(BaseType::INTEGER);
		$processor = new MemberProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		// fist operand is null.
		$operands->reset();
		$operands[] = null;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(10));
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testDifferentBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Pair('A', 'B');
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2)));
		$processor = new MemberProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(13, 37)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2)));
		$processor = new MemberProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Point(13, 37);
		$operands[] = new Point(13, 38);
		$processor = new MemberProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Point(13, 37);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new MemberProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Point(13, 37);
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(1, 2)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new Point(3, 4)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new MemberProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<member>
				<baseValue baseType="boolean">true</baseValue>
				<ordered>
					<baseValue baseType="boolean">false</baseValue>
					<baseValue baseType="boolean">true</baseValue>
				</ordered>
			</member>
		');
	}
}