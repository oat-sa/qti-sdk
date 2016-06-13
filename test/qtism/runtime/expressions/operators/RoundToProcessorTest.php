<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\RoundToProcessor;

class RoundToProcessorTest extends QtiSmTestCase {
	
	public function testSignificantFigures() {
		$expr = $this->createComponentFromXml('
			<roundTo figures="3">
				<baseValue baseType="float">1239451</baseValue>
			</roundTo>
		');
		
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(1239451);
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(round(1240000), round($result->getValue()));
		
		$operands[0] = new QtiFloat(12.1257);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(round(12.1, 1), round($result->getValue(), 1));
		
		$operands[0] = new QtiFloat(0.0681);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(round(0.0681, 4), round($result->getValue(), 4));
		
		$operands[0] = new QtiInteger(5);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(5, $result->getValue());
		
		$operands[0] = new QtiInteger(0);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands[0] = new QtiFloat(-12.1257);
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(round(-12.1, 1), round($result->getValue(), 1));
	}
	
	public function testDecimalPlaces() {
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		
		$operands = new OperandsCollection();
		$operands[] = new QtiFloat(3.4);
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(3, $result->getValue());
		
		$operands[0] = new QtiFloat(3.5);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(4, $result->getValue());
		
		$operands[0] = new QtiFloat(3.6);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(4, $result->getValue());
		
		$operands[0] = new QtiFloat(4.0);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(4, $result->getValue());
		
		$expr->setFigures(2); // We now go for 2 figures...
		$operands[0] = new QtiFloat(1.95583);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(1.96, $result->getValue());
		
		$operands[0] = new QtiFloat(5.045);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(5.05, $result->getValue());
		
		$expr->setFigures(2);
		$operands[0] = new QtiFloat(5.055);
		$result = $processor->process();
		$this->assertInstanceOf(QtiFloat::class, $result);
		$this->assertEquals(5.06, $result->getValue());
	}
	
	public function testNoOperands() {
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection();
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
	}
	
	public function testTooMuchOperands() {
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection(array(new QtiInteger(4), new QtiInteger(4)));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
	}
	
	public function testWrongBaseType() {
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection(array(new QtiBoolean(true)));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(20), new QtiInteger(30), new QtiInteger(40)))));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
	}
	
	public function testWrongFiguresOne() {
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="significantFigures">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		
		$operands = new OperandsCollection(array(new QtiFloat(3.4)));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
	}
	
	public function testWrongFiguresTwo() {
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		
		$expr = $this->createComponentFromXml('
			<roundTo figures="-1" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection(array(new QtiFloat(3.4)));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
	}
	
	public function testNan() {
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection(array(new QtiFloat(NAN)));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
		$this->assertTrue(is_null($result));
	}
	
	public function testInfinity() {
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection(array(new QtiFloat(INF)));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
		$this->assertTrue(is_infinite($result->getValue()));
		$this->assertTrue(INF === $result->getValue());
		
		$processor->setOperands(new OperandsCollection(array(new QtiFloat(-INF))));
		$result = $processor->process();
		$this->assertTrue(is_infinite($result->getValue()));
		$this->assertTrue(-INF === $result->getValue());
	}
}
