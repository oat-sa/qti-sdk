<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\processing\RandomFloatProcessor;

class RandomFloatProcessorTest extends QtiSmTestCase {
	
	public function testSimple() {
		$expression = $this->createComponentFromXml('<randomFloat max="100.34"/>');
		$processor = new RandomFloatProcessor($expression);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertLessThanOrEqual(100.34, $result);
		$this->assertGreaterThanOrEqual(0, $result);
		
		$expression = $this->createComponentFromXml('<randomFloat min="-2000" max="-1000"/>');
		$processor->setExpression($expression);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertGreaterThanOrEqual(-2000, $result);
		$this->assertLessThanOrEqual(-1000, $result);
		
		$expression = $this->createComponentFromXml('<randomFloat min="100" max="2430.6666"/>');
		$processor->setExpression($expression);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertGreaterThanOrEqual(100, $result);
		$this->assertLessThanOrEqual(2430.6666, $result);
	}
	
	public function testMinGreaterThanMax() {
		$expression = $this->createComponentFromXml('<randomFloat min="133.2" max="25.3"/>');
		$processor = new RandomFloatProcessor($expression);
		$processor->setExpression($expression);
		
		$this->setExpectedException('qtism\runtime\expressions\processing\ExpressionProcessingException');
		$result = $processor->process();
	}
}