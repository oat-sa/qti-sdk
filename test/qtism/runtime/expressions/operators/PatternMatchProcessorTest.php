<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\runtime\common\RecordContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\PatternMatchProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\OperatorProcessingException;

class PatternMatchProcessorTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider patternMatchProvider
	 * 
	 * @param string $string
	 * @param string $pattern
	 * @param boolean $expected
	 */
	public function testPatternMatch($string, $pattern, $expected) {
		$expression = $this->createFakeExpression($pattern);
		$operands = new OperandsCollection(array($string));
		$processor = new PatternMatchProcessor($expression, $operands);
		$this->assertSame($expected, $processor->process()->getValue());
	}
	
	/**
	 * @dataProvider nullProvider
	 * 
	 * @param string $string
	 * @param string $pattern
	 */
	public function testNull($string, $pattern) {
		$expression = $this->createFakeExpression($pattern);
		$operands = new OperandsCollection(array($string));
		$processor = new PatternMatchProcessor($expression, $operands);
		$this->assertSame(null, $processor->process());
	}
	
	public function testNotEnougOperands() {
		$expression = $this->createFakeExpression('abc');
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$processor = new PatternMatchProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression('abc');
		$operands = new OperandsCollection(array(new QtiString('string'), new QtiString('string')));
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$processor = new PatternMatchProcessor($expression, $operands);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression('abc');
		$operands = new OperandsCollection(array(new RecordContainer(array('A' => new QtiInteger(1)))));
		$processor = new PatternMatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression('abc');
		$operands = new OperandsCollection(array(new QtiFloat(255.34)));
		$processor = new PatternMatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\operators\\OperatorProcessingException');
		$result = $processor->process();
	}
	
	public function testInternalError() {
		$expression = $this->createFakeExpression('[');
		$operands = new OperandsCollection(array(new QtiString('string!')));
		$processor = new PatternMatchProcessor($expression, $operands);
		try {
			$result = $processor->process();
			$this->assertFalse(true);
		}
		catch (OperatorProcessingException $e) {
			$this->assertTrue(true);
			$this->assertEquals(OperatorProcessingException::RUNTIME_ERROR, $e->getCode());
		}
	}
	
	public function patternMatchProvider() {
		return array(
			array(new QtiString('string'), 'string', true),
			array(new QtiString('string'), 'stRing', false),
			array(new QtiString('string'), 'shell', false),
			array(new QtiString('stringString'), '.*', true), // in xml schema 2, dot matches white-spaces
			array(new QtiString('^String$'), 'String', false), // No carret nor dollar in xml schema 2
			array(new QtiString('^String$'), '^String$', true),
			array(new QtiString('Str/ing'), 'Str/ing', true),
			array(new QtiString('Str^ing'), 'Str^ing', true),
			array(new QtiString('99'), '\d{1,2}', true),
			array(new QtiString('abc'), '\d{1,2}', false)
		);
	}
	
	public function nullProvider() {
		return array(
			array(null, '\d{1,2}'),
			array(new QtiString(''), '\d{1,2}'),
			array(new OrderedContainer(BaseType::STRING), '\d{1,2}')
		);
	}
	
	public function createFakeExpression($pattern) {
		return $this->createComponentFromXml('
			<patternMatch pattern="' . $pattern . '">
				<baseValue baseType="string">String!</baseValue>
			</patternMatch>
		');
	}
}
