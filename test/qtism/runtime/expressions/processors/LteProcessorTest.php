<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\Point;
use qtism\runtime\expressions\processing\LteProcessor;
use qtism\runtime\expressions\processing\OperandsCollection;

class LteProcessorTest extends QtiSmTestCase {
	
	public function testLte() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 0.5;
		$operands[] = 1;
		$processor = new LteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		$operands->reset();
		$operands[] = 1;
		$operands[] = 0.5;
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
		
		$operands->reset();
		$operands[] = 1;
		$operands[] = 1;
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 1;
		$operands[] = null;
		$processor = new LteProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 1;
		$operands[] = true;
		$processor = new LteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Point(1, 2);
		$operands[] = 2;
		$processor = new LteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new RecordContainer(array('A' => 1));
		$operands[] = 2;
		$processor = new LteProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new LteProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(1, 2, 3));
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new LteProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<lte>
				<baseValue baseType="float">9.9</baseValue>
				<baseValue baseType="integer">10</baseValue>
			</lte>
		');
	}
}