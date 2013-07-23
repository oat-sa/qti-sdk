<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\Point;
use qtism\common\datatypes\Duration;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\processing\RandomProcessor;
use qtism\runtime\expressions\processing\OperandsCollection;

class RandomProcessorTest extends QtiSmTestCase {
	
	public function testPrimitiveMultiple() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertGreaterThanOrEqual(1.0, $result);
		$this->assertLessThanOrEqual(3.0, $result);
	}
	
	public function testPrimitiveOrdered() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array('s1', 's2', 's3'));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('string', $result);
		$this->assertTrue($result === 's1' || $result === 's2' || $result === 's3');
	}
	
	public function testComplexMultiple() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::DURATION, array(new Duration('P1D'), new Duration('P2D'), new Duration('P3D')));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\Duration', $result);
		$this->assertGreaterThanOrEqual(1, $result->getDays());
		$this->assertLessThanOrEqual(3, $result->getDays());
	}
	
	public function testComplexOrdered() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 2), new Point(3, 3)));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\Point', $result);
		$this->assertGreaterThanOrEqual(1, $result->getX());
		$this->assertLessThanOrEqual(3, $result->getY());
	}
	
	public function testOnlyOneInContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(22, 33)));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\Point', $result);
		$this->assertEquals(22, $result->getX());
		$this->assertEquals(33, $result->getY());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT);
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinalityOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10;
		$processor = new RandomProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new RecordContainer(array('A' => 1));
		$processor = new RandomProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::PAIR);
		$operands[] = new MultipleContainer(BaseType::PAIR);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<random>
				<multiple>
					<baseValue baseType="boolean">true</baseValue>
					<baseValue baseType="boolean">false</baseValue>
				</multiple>
			</random>
		');
	}
}