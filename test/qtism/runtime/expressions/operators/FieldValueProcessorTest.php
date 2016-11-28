<?php

use qtism\common\datatypes\QtiInteger;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\common\datatypes\QtiPoint;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\FieldValueProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class FieldValueProcessorTest extends QtiSmTestCase {
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new FieldValueProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new RecordContainer();
		$operands[] = new RecordContainer();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new FieldValueProcessor($expression, $operands);
	}
	
	public function testNullOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		
		// unexisting field in record.
		$operands[] = new RecordContainer();
		$processor = new FieldValueProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testNullTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		// null value as operand.
		$operands[] = null;
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new FieldValueProcessor($expression, $operands);
		$result = $processor->process();
	}
	
	public function testWrongCardinalityOne() {
		// primitive PHP.
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(10);
		$processor = new FieldValueProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		// primitive QTI (Point, Duration, ...)
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPoint(1, 2);
		$processor = new FieldValueProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityThree() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2)));
		
		// Wrong container (Multiple, Ordered)
		$processor = new FieldValueProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testFieldValue() {
		$expression = $this->createFakeExpression('B');
		
		$operands = new OperandsCollection();
		$operands[] = new RecordContainer(array('A' => new QtiInteger(1), 'B' => new QtiInteger(2), 'C' => new QtiInteger(3)));
		$processor = new FieldValueProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertEquals(2, $result->getValue());
		
		$expression = $this->createFakeExpression('D');
		$processor->setExpression($expression);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function createFakeExpression($identifier = '') {
		// The following XML Component creation
		// underlines the need of a <record> operator... :)
		// -> <multiple> used here just for the example,
		// this is not valid.
		if (empty($identifier)) {
			$identifier = 'identifier1';
		}
		
		return $this->createComponentFromXml('
			<fieldValue fieldIdentifier="' . $identifier . '">
				<multiple>
					<baseValue baseType="boolean">true</baseValue>
					<baseValue baseType="boolean">false</baseValue>
				</multiple>
			</fieldValue>
		');
	}
}
