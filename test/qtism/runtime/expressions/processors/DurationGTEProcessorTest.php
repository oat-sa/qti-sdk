<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\common\datatypes\Duration;
use qtism\runtime\expressions\processing\DurationGTEProcessor;
use qtism\runtime\expressions\processing\OperandsCollection;

class DurationGTEProcessorTest extends QtiSmTestCase {
	
	public function testDurationGTE() {
		// There is no need of intensive testing because
		// the main logic is contained in the Duration class.
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new Duration('P2D'), new Duration('P1D')));
		$processor = new DurationGTEProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		$operands = new OperandsCollection(array(new Duration('P1D'), new Duration('P2D')));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
		
		$operands = new OperandsCollection(array(new Duration('P1DT23M2S'), new Duration('P1DT23M2S')));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new Duration('P1D'), null));
		$processor = new DurationGTEProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new Duration('P1D'), 256));
		$processor = new DurationGTEProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new Duration('P1D'), new MultipleContainer(BaseType::DURATION, array(new Duration('P2D')))));
		$processor = new DurationGTEProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new DurationGTEProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new Duration('P1D'), new Duration('P2D'), new Duration('P3D')));
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$processor = new DurationGTEProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<durationGTE>
				<baseValue baseType="duration">P2D</baseValue>
				<baseValue baseType="duration">P1D</baseValue>
			</durationGTE>
		');
	}
}