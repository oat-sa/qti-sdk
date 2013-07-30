<?php


use qtism\common\enums\BaseType;

use qtism\runtime\common\MultipleContainer;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

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
		$operands[] = 1239451;
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1240000, $result);
		
		$operands[0] = 12.1257;
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(12.1, $result);
		
		$operands[0] = 0.0681;
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(0.0681, $result);
		
		$operands[0] = 5;
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(5, $result);
		
		$operands[0] = 0;
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(0, $result);
		
		$operands[0] = -12.1257;
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(-12.1, $result);
	}
	
	public function testDecimalPlaces() {
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection();
		$operands[] = 3.4;
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(3, $result);
		
		$operands[0] = 3.5;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(4, $result);
		
		$operands[0] = 3.6;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(4, $result);
		
		$operands[0] = 4.0;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(4, $result);
		
		$expr->setFigures(2); // We now go for 2 figures...
		$operands[0] = 1.95583;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1.96, $result);
		
		$operands[0] = 5.045;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(5.05, $result);
		
		$expr->setFigures(2);
		$operands[0] = 5.055;
		$result = $processor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(5.06, $result);
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
		$operands = new OperandsCollection(array(4, 4));
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
		$operands = new OperandsCollection(array(true));
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
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER, array(20, 30, 40))));
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
		
		$operands = new OperandsCollection(array(3.4));
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
		$operands = new OperandsCollection(array(3.4));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
	}
	
	public function testNan() {
		$expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
		$operands = new OperandsCollection(array(NAN));
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
		$operands = new OperandsCollection(array(INF));
		$processor = new RoundToProcessor($expr, $operands);
		$result = $processor->process();
		$this->assertTrue(is_infinite($result));
		$this->assertTrue(INF === $result);
		
		$processor->setOperands(new OperandsCollection(array(-INF)));
		$result = $processor->process();
		$this->assertTrue(is_infinite($result));
		$this->assertTrue(-INF === $result);
	}
}