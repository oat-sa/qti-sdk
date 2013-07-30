<?php

use qtism\common\enums\BaseType;

use qtism\runtime\common\MultipleContainer;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\operators\SubstringProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class SubstringProcessorTest extends QtiSmTestCase {
	
	public function testCaseSensitive() {
		$expression = $this->createFakeExpression(true);
		$operands = new OperandsCollection();
		$operands[] = 'hell';
		$operands[] = 'Shell';
		$processor = new SubstringProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		$operands->reset();
		$operands[] = 'Hell';
		$operands[] = 'Shell';
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testCaseInsensitive() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = 'hell';
		$operands[] = 'Shell';
		$processor = new SubstringProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		$operands->reset();
		$operands[] = 'Hell';
		$operands[] = 'Shell';
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		$operands->reset();
		$operands[] = 'Hello world!';
		$operands[] = 'Bye world!';
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
		
		$operands->reset();
		$operands[] = 'Hello World!';
		$operands[] = 'hello world!';
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		// Unicode ? x)
		$operands->reset();
		$operands[] = '界您';
		$operands[] = '世界您好！'; // Hello World!
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertTrue($result);
		
		$operands->reset();
		$operands[] = '假'; // 'Fake' in traditional chinese
		$operands[] = '世界您好！'; // Hello World!
		$result = $processor->process();
		$this->assertInternalType('boolean', $result);
		$this->assertFalse($result);
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = 'test';
		$operands[] = null;
		$processor = new SubstringProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = ''; // in QTI, empty string considered to be NULL.
		$operands[] = 'blah!';
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = '10';
		$operands[] = 100;
		$processor = new SubstringProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = 'Wrong Cardinality';
		$operands[] = new MultipleContainer(BaseType::STRING, array('Wrong', 'Cardinality'));
		$processor = new SubstringProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection(array('only 1 operand'));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new SubstringProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection(array('exactly', 'three', 'operands'));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new SubstringProcessor($expression, $operands);
	}
	
	public function createFakeExpression($caseSensitive = true) {
		
		$str = ($caseSensitive === true) ? 'true' : 'false';
		
		return $this->createComponentFromXml('
			<substring caseSensitive="' . $str . '">
				<baseValue baseType="string">hell</baseValue>
				<baseValue baseType="string">shell</baseValue>
			</substring>
		');
	}
}