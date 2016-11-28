<?php

use qtism\common\datatypes\QtiUri;

use qtism\common\datatypes\QtiInteger;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\enums\Cardinality;
use qtism\runtime\common\OrderedContainer;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;

class OrderedContainerTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider equalsValidProvider
	 */
	public function testEqualsValid($containerA, $containerB) {
		$this->assertTrue($containerA->equals($containerB));
		$this->assertTrue($containerB->equals($containerA));
	}
	
	/**
	 * @dataProvider equalsInvalidProvider
	 */
	public function testEqualsInvalid($containerA, $containerB) {
		$this->assertFalse($containerA->equals($containerB));
		$this->assertFalse($containerB->equals($containerA));
	}
	
	public function testCreationEmpty() {
		$container = new OrderedContainer(BaseType::INTEGER);
		$this->assertEquals(0, count($container));
		$this->assertEquals(BaseType::INTEGER, $container->getBaseType());
		$this->assertEquals(Cardinality::ORDERED, $container->getCardinality());
	}
	
	public function equalsValidProvider() {
		return array(
			array(new OrderedContainer(BaseType::INTEGER), new OrderedContainer(BaseType::INTEGER)),
			array(new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(20))), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(20)))),
			array(new OrderedContainer(BaseType::URI, array(new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.tao.lu'))), new OrderedContainer(BaseType::URI, array(new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.tao.lu')))),
			array(new OrderedContainer(BaseType::PAIR, array(new QtiPair('abc', 'def'))), new OrderedContainer(BaseType::PAIR, array(new QtiPair('def', 'abc'))))
		);
	}
	
	public function equalsInvalidProvider() {
		return array(
			array(new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(20))), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(30)))),
			array(new OrderedContainer(BaseType::URI, array(new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.tao.lu'))), new OrderedContainer(BaseType::URI, array(new QtiUri('http://www.tao.lu'), new QtiUri('http://www.taotesting.com')))),
			array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('abc', 'def'))), new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('def', 'abc')))),
		);
	}
}
