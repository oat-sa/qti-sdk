<?php
use qtism\common\datatypes\Integer;
use qtism\runtime\expressions\operators\OperandsCollection;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\operators\OperatorProcessorFactory;
use qtism\data\expressions\operators\Operator;

class OperatorProcessorFactoryTest extends QtiSmTestCase {
	
	public function testCreateProcessor() {
		// get a fake sum expression.
		$expression = $this->createComponentFromXml(
			'<sum>
				<baseValue baseType="integer">2</baseValue>
				<baseValue baseType="integer">2</baseValue>
			</sum>'
		);
		
		$factory = new OperatorProcessorFactory();
		$operands = new OperandsCollection(array(new Integer(2), new Integer(2)));
		$processor = $factory->createProcessor($expression, $operands);
		$this->assertInstanceOf('qtism\\runtime\\expressions\\operators\\SumProcessor', $processor);
		$this->assertEquals('sum', $processor->getExpression()->getQtiClassName());
		$this->assertEquals(4, $processor->process()->getValue()); // x)
	}
	
	public function testInvalidOperatorClass() {
		$expression = $this->createComponentFromXml('<baseValue baseType="string">String!</baseValue>');
		$factory = new OperatorProcessorFactory();
		
		$this->setExpectedException('\\InvalidArgumentException');
		$processor = $factory->createProcessor($expression);
	}
}