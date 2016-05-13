<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\SubstringProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class SubstringProcessorTest extends QtiSmTestCase {
	
	public function testCaseSensitive() {
		$expression = $this->createFakeExpression(true);
		$operands = new OperandsCollection();
		$operands[] = new QtiString('hell');
		$operands[] = new QtiString('Shell');
		$processor = new SubstringProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		$operands->reset();
		$operands[] = new QtiString('Hell');
		$operands[] = new QtiString('Shell');
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testCaseInsensitive() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = new QtiString('hell');
		$operands[] = new QtiString('Shell');
		$processor = new SubstringProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		$operands->reset();
		$operands[] = new QtiString('Hell');
		$operands[] = new QtiString('Shell');
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		$operands->reset();
		$operands[] = new QtiString('Hello world!');
		$operands[] = new QtiString('Bye world!');
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
		
		$operands->reset();
		$operands[] = new QtiString('Hello World!');
		$operands[] = new QtiString('hello world!');
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		// Unicode ? x)
		$operands->reset();
		$operands[] = new QtiString('界您');
		$operands[] = new QtiString('世界您好！'); // Hello World!
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertTrue($result->getValue());
		
		$operands->reset();
		$operands[] = new QtiString('假'); // 'Fake' in traditional chinese
		$operands[] = new QtiString('世界您好！'); // Hello World!
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertFalse($result->getValue());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = new QtiString('test');
		$operands[] = null;
		$processor = new SubstringProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = new QtiString(''); // in QTI, empty string considered to be NULL.
		$operands[] = new QtiString('blah!');
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = new QtiString('10');
		$operands[] = new QtiInteger(100);
		$processor = new SubstringProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection();
		$operands[] = new QtiString('Wrong Cardinality');
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('Wrong'), new QtiString('Cardinality')));
		$processor = new SubstringProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection(array(new QtiString('only 1 operand')));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new SubstringProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression(false);
		$operands = new OperandsCollection(array(new QtiString('exactly'), new QtiString('three'), new QtiString('operands')));
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
