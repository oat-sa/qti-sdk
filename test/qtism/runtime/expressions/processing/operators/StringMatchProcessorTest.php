<?php
use qtism\common\enums\BaseType;

use qtism\runtime\common\MultipleContainer;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\operators\StringMatchProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\data\expressions\operators\Operator;

class StringMatchProcessorTest extends QtiSmTestCase {
	
	public function testStringMatch() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('one', 'one'));
		$processor = new StringMatchProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertSame(true, $result);
		
		$operands = new OperandsCollection(array('one', 'oNe'));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertSame(false, $result);
		
		$processor->setExpression($this->createFakeExpression(false));
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertSame(true, $result);
		
		// Binary-safe?
		$processor->setExpression($this->createFakeExpression(true));
		$operands = new OperandsCollection(array('它的工作原理', '它的工作原理'));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertSame(true, $result);
		
		$operands = new OperandsCollection(array('它的工作原理', '它的原理'));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertSame(false, $result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('', null));
		$processor = new StringMatchProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('String!', new MultipleContainer(BaseType::STRING, array('String!'))));
		$processor = new StringMatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('String!', 25));
		$processor = new StringMatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('String!'));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new StringMatchProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array('String!', 'String!', 'String!'));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new StringMatchProcessor($expression, $operands);
	}
	
	public function createFakeExpression($caseSensitive = true) {
		
		$str = ($caseSensitive === true) ? 'true' : 'false';
		
		return $this->createComponentFromXml('
			<stringMatch caseSensitive="' . $str . '">
				<baseValue baseType="string">This does</baseValue>
				<baseValue baseType="string">not match</baseValue>
			</stringMatch>
		');
	}
}