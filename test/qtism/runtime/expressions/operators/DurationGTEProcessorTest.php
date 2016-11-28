<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\common\datatypes\QtiDuration;
use qtism\runtime\expressions\operators\DurationGTEProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class DurationGTEProcessorTest extends QtiSmTestCase {
	
	public function testDurationGTE() {
		// There is no need of intensive testing because
		// the main logic is contained in the Duration class.
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiDuration('P2D'), new QtiDuration('P1D')));
		$processor = new DurationGTEProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		$operands = new OperandsCollection(array(new QtiDuration('P1D'), new QtiDuration('P2D')));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
		
		$operands = new OperandsCollection(array(new QtiDuration('P1DT23M2S'), new QtiDuration('P1DT23M2S')));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiDuration('P1D'), null));
		$processor = new DurationGTEProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiDuration('P1D'), new QtiInteger(256)));
		$processor = new DurationGTEProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiDuration('P1D'), new MultipleContainer(BaseType::DURATION, array(new QtiDuration('P2D')))));
		$processor = new DurationGTEProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new DurationGTEProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiDuration('P1D'), new QtiDuration('P2D'), new QtiDuration('P3D')));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
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
