<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\processing\OperandsCollection;
use qtism\runtime\expressions\processing\ProductProcessor;

class ProductProcessorTest extends QtiSmTestCase {

	public function testSimple() {
		$product = $this->createFakeProductComponent();
		
		$operands = new OperandsCollection(array(1, 1));
		$productProcessor = new ProductProcessor($product, $operands);
		$result = $productProcessor->process();
		
		$this->assertInstanceOf('qtism\\runtime\\common\\Processable', $productProcessor);
		$this->assertInternalType('integer', $result);
		$this->assertEquals(1, $result);
	}
	
	public function testNary() {
		$product = $this->createFakeProductComponent();
		
		$operands = new OperandsCollection(array(24, -4, 1));
		$productProcessor = new ProductProcessor($product, $operands);
		$result = $productProcessor->process();

		$this->assertInternalType('integer', $result);
		$this->assertEquals(-96, $result);
	}
	
	public function testComplex() {
		$product = $this->createFakeProductComponent();
		
		$operands = new OperandsCollection(array(-1, 1));
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(2.1, 4.3));
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(10, 15));
		$productProcessor = new ProductProcessor($product, $operands);
		$result = $productProcessor->process();
		
		$this->assertInternalType('float', $result);
		$this->assertEquals(-1354.5, $result);
	}
	
	public function testInvalidOperandsOne() {
		$product = $this->createFakeProductComponent();
		
		$this->setExpectedException('\\RuntimeException');
		
		$operands = new OperandsCollection(array(true, 14, 10));
		$productProcessor = new ProductProcessor($product, $operands);
		$result = $productProcessor->process();
	}
	
	public function testInvalidOperandsTwo() {
		$product = $this->createFakeProductComponent();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::BOOLEAN, array(true, false));
		$productProcessor = new ProductProcessor($product, $operands);
		
		$this->setExpectedException('\\RuntimeException');
		$result = $productProcessor->process();
	}
	
	public function testNullInvolved() {
		$product = $this->createFakeProductComponent();
		$operands = new OperandsCollection(array(10, 10, null));
		$productProcessor = new ProductProcessor($product, $operands);
		$result = $productProcessor->process();
		$this->assertTrue($result === null);
	}
	
	public function testNotEnoughOperands() {
		$product = $this->createFakeProductComponent();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\processing\\ExpressionProcessingException');
		$productProcessor = new ProductProcessor($product, $operands);
	}
	
	private function createFakeProductComponent() {
		return $this->createComponentFromXml('
			<product xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">3</baseValue>
			</product>
		');
	}
}