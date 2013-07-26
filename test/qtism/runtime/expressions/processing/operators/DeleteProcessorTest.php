<?php
require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Point;
use qtism\runtime\expressions\processing\operators\DeleteProcessor;
use qtism\runtime\expressions\processing\operators\OperandsCollection;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;

class DeleteProcessorTest extends QtiSmTestCase {
	
	public function testMultiple() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(0, 10, 20, 30));
		$processor = new DeleteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $result);
		$this->assertEquals(3, count($result));
		$this->assertTrue($result->contains(0));
		$this->assertTrue($result->contains(20));
		$this->assertTrue($result->contains(30));
		$this->assertFalse($result->contains(10));
		
		// Check that ALL the occurences of the first sub-expression are removed.
		$operands->reset();
		$operands[] = 10;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(0, 10, 20, 10, 10, 30));
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $result);
		$this->assertEquals(3, count($result));
		$this->assertTrue($result->contains(0));
		$this->assertTrue($result->contains(20));
		$this->assertTrue($result->contains(30));
		$this->assertFalse($result->contains(10));
	}
	
	public function testMultipleNotMatch() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 60;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(0, 10, 20, 30));
		$processor = new DeleteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertTrue($operands[1]->equals($result));
	}
	
	public function testEverythingRemoved() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 60;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(60, 60, 60, 60));
		$processor = new DeleteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $result);
		$this->assertTrue($result->isNull());
	}
	
	public function testOrdered() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Point(2, 4);
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(2, 4), new Point(3, 4)));
		$processor = new DeleteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(2, count($result));
		$this->assertTrue($result->contains(new Point(1, 2)));
		$this->assertTrue($result->contains(new Point(3, 4)));
		$this->assertFalse($result->contains(new Point(2, 4)));
	
		// Check that ALL the occurences of the first sub-expression are removed.
		$operands->reset();
		$operands[] = new Point(2, 4);
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(2, 4), new Point(2, 4), new Point(3, 4)));
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(2, count($result));
		$this->assertTrue($result->contains(new Point(1, 2)));
		$this->assertTrue($result->contains(new Point(3, 4)));
		$this->assertFalse($result->contains(new Point(2, 4)));
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = null;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(0, 10, 20, 30));
		$processor = new DeleteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = 10;
		$operands[] = new MultipleContainer(BaseType::INTEGER);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testDifferentBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10.1;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(0, 10, 20, 30));
		$processor = new DeleteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(0, 10, 20, 30));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(0, 10, 20, 30));
		$processor = new DeleteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10;
		$operands[] = 10;
		$processor = new DeleteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new DeleteProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(10));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(10));
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new DeleteProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<delete>
				<baseValue baseType="integer">10</baseValue>
				<multiple>
					<baseValue baseType="integer">0</baseValue>
					<baseValue baseType="integer">10</baseValue>
					<baseValue baseType="integer">20</baseValue>
					<baseValue baseType="integer">30</baseValue>
				</multiple>
			</delete>
		');
	}
}