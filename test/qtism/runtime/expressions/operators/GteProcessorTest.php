<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\QtiPoint;
use qtism\runtime\expressions\operators\GteProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class GteProcessorTest extends QtiSmTestCase {
	
	public function testGte() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(1);
		$operands[] = new QtiFloat(0.5);
		$processor = new GteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(0.5);
		$operands[] = new QtiInteger(1);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(1);
		$operands[] = new QtiInteger(1);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(1);
		$operands[] = null;
		$processor = new GteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(1);
		$operands[] = new QtiBoolean(true);
		$processor = new GteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPoint(1, 2);
		$operands[] = new QtiInteger(2);
		$processor = new GteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new RecordContainer(array('A' => new QtiInteger(1)));
		$operands[] = new QtiInteger(2);
		$processor = new GteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new GteProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(1), new QtiInteger(2), new QtiInteger(3)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new GteProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<gte>
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="float">9.9</baseValue>
			</gte>
		');
	}
}
