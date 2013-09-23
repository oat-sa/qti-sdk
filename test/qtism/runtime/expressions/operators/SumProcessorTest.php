<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\SumProcessor;

class SumProcessorTest extends QtiSmTestCase {

	public function testSimple() {
		$sum = $this->createFakeSumComponent();
		
		$operands = new OperandsCollection(array(1, 1));
		$sumProcessor = new SumProcessor($sum, $operands);
		$result = $sumProcessor->process();
		
		$this->assertInstanceOf('qtism\\runtime\\common\\Processable', $sumProcessor);
		$this->assertInternalType('integer', $result);
		$this->assertEquals(2, $result);
	}
	
	public function testNary() {
		$sum = $this->createFakeSumComponent();
		
		$operands = new OperandsCollection(array(24, -4, 0));
		$sumProcessor = new SumProcessor($sum, $operands);
		$result = $sumProcessor->process();

		$this->assertInternalType('integer', $result);
		$this->assertEquals(20, $result);
	}
	
	public function testComplex() {
		$sum = $this->createFakeSumComponent();
		
		$operands = new OperandsCollection(array(-1, 1));
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(2.1, 4.3));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(10, 15));
		$sumProcessor = new SumProcessor($sum, $operands);
		$result = $sumProcessor->process();
		
		$this->assertInternalType('float', $result);
		$this->assertEquals(31.4, $result);
	}
	
	public function testZero() {
	    $sum = $this->createFakeSumComponent();
	    
	    $operands = new OperandsCollection(array(0, 6.0));
	    $sumProcessor = new SumProcessor($sum, $operands);
	    $result = $sumProcessor->process();
	    
	    $this->assertInternalType('float', $result);
	    $this->assertEquals(6.0, $result);
	}
	
	public function testInvalidOperandsOne() {
		$sum = $this->createFakeSumComponent();
		
		$this->setExpectedException('\\RuntimeException');
		
		$operands = new OperandsCollection(array(true, 14, 10));
		$sumProcessor = new SumProcessor($sum, $operands);
		$result = $sumProcessor->process();
	}
	
	public function testInvalidOperandsTwo() {
		$sum = $this->createFakeSumComponent();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::BOOLEAN, array(true, false));
		$sumProcessor = new SumProcessor($sum, $operands);
		
		$this->setExpectedException('\\RuntimeException');
		$result = $sumProcessor->process();
	}
	
	public function testNullInvolved() {
		$sum = $this->createFakeSumComponent();
		$operands = new OperandsCollection(array(10, 10, null));
		$sumProcessor = new SumProcessor($sum, $operands);
		$result = $sumProcessor->process();
		$this->assertTrue($result === null);
	}
	
	private function createFakeSumComponent() {
		$sum = $this->createComponentFromXml('
			<sum xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">3</baseValue>
			</sum>
		');
		
		return $sum;
	}
}