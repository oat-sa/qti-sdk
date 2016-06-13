<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiFloat;
use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiDuration;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\RandomProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class RandomProcessorTest extends QtiSmTestCase {
	
	public function testPrimitiveMultiple() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(2.0), new QtiFloat(3.0)));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertGreaterThanOrEqual(1.0, $result->getValue());
		$this->assertLessThanOrEqual(3.0, $result->getValue());
	}
	
	public function testPrimitiveOrdered() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('s1'), new QtiString('s2'), new QtiString('s3')));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiString::class, $result);
		$this->assertTrue($result->equals(new QtiString('s1')) || $result->equals(new QtiString('s2')) || $result->equals(new QtiString('s3')));
	}
	
	public function testComplexMultiple() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::DURATION, array(new QtiDuration('P1D'), new QtiDuration('P2D'), new QtiDuration('P3D')));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiDuration::class, $result);
		$this->assertGreaterThanOrEqual(1, $result->getDays());
		$this->assertLessThanOrEqual(3, $result->getDays());
	}
	
	public function testComplexOrdered() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 1), new QtiPoint(2, 2), new QtiPoint(3, 3)));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiPoint::class, $result);
		$this->assertGreaterThanOrEqual(1, $result->getX());
		$this->assertLessThanOrEqual(3, $result->getY());
	}
	
	public function testOnlyOneInContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new QtiPoint(22, 33)));
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiPoint::class, $result);
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
		$operands[] = new QtiInteger(10);
		$processor = new RandomProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new RecordContainer(array('A' => new QtiInteger(1)));
		$processor = new RandomProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new RandomProcessor($expression, $operands);
		$result = $processor->process();
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::PAIR);
		$operands[] = new MultipleContainer(BaseType::PAIR);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
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
