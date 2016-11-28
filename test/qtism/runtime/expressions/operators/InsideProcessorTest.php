<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiShape;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiPoint;
use qtism\runtime\expressions\operators\InsideProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class InsideProcessorTest extends QtiSmTestCase {
	
	public function testRect() {
		$coords = new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3));
		$point = new QtiPoint(0, 0); // 0, 0 is inside.
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array($point));
		$processor = new InsideProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		$point = new QtiPoint(-1, -1); // -1, -1 is outside.
		$operands = new OperandsCollection(array($point));
		$expression = $this->createFakeExpression($point, $coords);
		$processor->setExpression($expression);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testPoly() {
		$coords = new QtiCoords(QtiShape::POLY, array(0, 8, 7, 4, 2, 2, 8, -4, -2, 1));
		$point = new QtiPoint(0, 8); // 0, 8 is inside.
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array($point));
		$processor = new InsideProcessor($expression, $operands);
	
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		$point = new QtiPoint(10, 9); // 10, 9 is outside.
		$operands = new OperandsCollection(array($point));
		$expression = $this->createFakeExpression($point, $coords);
		$processor->setExpression($expression);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testCircle() {
		$coords = new QtiCoords(QtiShape::CIRCLE, array(5, 5, 5));
		$point = new QtiPoint(3, 3); // 3,3 is inside
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array($point));
		$processor = new InsideProcessor($expression, $operands);
	
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	
		$point = new QtiPoint(1, 1); // 1,1 is outside
		$operands = new OperandsCollection(array($point));
		$expression = $this->createFakeExpression($point, $coords);
		$processor->setExpression($expression);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testNull() {
		$coords = new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3));
		$point = null;
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array($point));
		$processor = new InsideProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseTypeOne() {
		$coords = new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3));
		$point = new QtiDuration('P1D');
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array($point));
		$processor = new InsideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$coords = new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3));
		$point = new QtiInteger(10);
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array($point));
		$processor = new InsideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$coords = new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3));
		$point = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2)));
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array($point));
		$processor = new InsideProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$coords = new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3));
		$point = new QtiPoint(1, 2);
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new InsideProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$coords = new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3));
		$point = new QtiPoint(1, 2);
		$expression = $this->createFakeExpression($point, $coords);
		$operands = new OperandsCollection(array(new QtiPoint(1, 2), new QtiPoint(2, 3)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new InsideProcessor($expression, $operands);
	}
	
	public function createFakeExpression($point = null, QtiCoords $coords = null) {
		$point = (is_null($point) || !$point instanceof QtiPoint) ? new QtiPoint(2, 2) : $point;
		$coords = (is_null($coords)) ? new QtiCoords(QtiShape::RECT, array(0, 0, 5, 3)) : $coords;
		
		return $this->createComponentFromXml('
			<inside shape="' . QtiShape::getNameByConstant($coords->getShape()) . '" coords="' . $coords . '">
				<baseValue baseType="point">' . $point . '</baseValue>
			</inside>
		');
	}
}
