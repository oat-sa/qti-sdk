<?php

use qtism\common\datatypes\QtiString;

use qtism\common\datatypes\QtiFloat;

use qtism\common\datatypes\QtiInteger;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\operators\ContainerSizeProcessor;
use qtism\data\expressions\operators\ContainerSize; 
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\common\datatypes\QtiPoint;
use qtism\runtime\common\RecordContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;

class ContainerSizeProcessorTest extends QtiSmTestCase {
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new ContainerSizeProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(25)));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(26)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new ContainerSizeProcessor($expression, $operands);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(null));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertSame(0, $result->getValue());
		
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertSame(0, $result->getValue());
	}
	
	public function testWrongCardinalityOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(25)));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new RecordContainer(array('1' => new QtiFloat(1.0), '2' => new QtiInteger(2)))));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testSize() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('String!')));
		$processor = new ContainerSizeProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEquals(1, $result->getValue());
		
		$operands->reset();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2), new QtiPoint(2, 3), new QtiPoint(3, 4)));
		$result = $processor->process();
		$this->assertInstanceOf(QtiInteger::class, $result);
		$this->assertEquals(3, $result->getValue());
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
