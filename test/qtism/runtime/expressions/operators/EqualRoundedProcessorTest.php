<?php
use qtism\common\enums\BaseType;

use qtism\common\enums\Cardinality;

use qtism\runtime\common\OutcomeVariable;

use qtism\runtime\common\State;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\Pair;
use qtism\data\expressions\operators\RoundingMode;
use qtism\runtime\expressions\operators\EqualRoundedProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class EqualRoundedProcessorTest extends QtiSmTestCase {
	
	public function testSignificantFigures() {
		$expression = $this->createFakeExpression(RoundingMode::SIGNIFICANT_FIGURES, 3);
		$operands = new OperandsCollection(array(3.175, 3.183));
		$processor = new EqualRoundedProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(true, $result);
		
		$operands = new OperandsCollection(array(3.175, 3.1749));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertSame(false, $result);
	}
	
	public function testDecimalPlaces() {
		$expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
		$operands = new OperandsCollection(array(1.68572, 1.69));
		$processor = new EqualRoundedProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(true, $result);
		
		$operands = new OperandsCollection(array(1.68572, 1.68432));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertSame(false, $result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
		$operands = new OperandsCollection(array(1.68572, null));
		$processor = new EqualRoundedProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testVariableRef() {
		$expression = $this->createFakeExpression(RoundingMode::SIGNIFICANT_FIGURES, 'var1');
		$operands = new OperandsCollection(array(3.175, 3.183));
		$processor = new EqualRoundedProcessor($expression, $operands);
		
		$state = new State();
		$state->setVariable(new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::INTEGER, 3));
		$processor->setState($state);
		
		$result = $processor->process();
		$this->assertSame(true, $result);
	}
	
	public function testUnknownVariableRef() {
		$expression = $this->createFakeExpression(RoundingMode::SIGNIFICANT_FIGURES, 'var1');
		$operands = new OperandsCollection(array(3.175, 3.183));
		$processor = new EqualRoundedProcessor($expression, $operands);
		
		$state = new State();
		$state->setVariable(new OutcomeVariable('varX', Cardinality::SINGLE, BaseType::INTEGER, 3));
		$processor->setState($state);
		
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
		$this->assertSame(true, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
		$operands = new OperandsCollection(array(new Pair('A', 'B'), 3));
		$processor = new EqualRoundedProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
		$operands = new OperandsCollection(array(10, new RecordContainer(array('A' => 1337))));
		$processor = new EqualRoundedProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
		$operands = new OperandsCollection(array(10));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new EqualRoundedProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
		$operands = new OperandsCollection(array(10, 10, 10));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new EqualRoundedProcessor($expression, $operands);
	}
	
	public function createFakeExpression($roundingMode, $figures) {
		
		$roundingMode = RoundingMode::getNameByConstant($roundingMode);
		
		return $this->createComponentFromXml('
			<equalRounded roundingMode="' . $roundingMode . '" figures="' . $figures . '">
				<baseValue baseType="float">102.155</baseValue>
				<baseValue baseType="float">1065.155</baseValue>
			</equalRounded>
		');
	}
}