<?php

use qtism\data\state\ValueCollection;
use qtism\data\state\Value;
use qtism\common\datatypes\Point;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;

class MultipleContainerTest extends QtiSmTestCase {
	
	public function testCreationEmpty() {
		$container = new MultipleContainer(BaseType::BOOLEAN);
		$this->assertEquals(BaseType::BOOLEAN, $container->getBaseType());
		$this->assertEquals(0, count($container));
		$this->assertEquals(Cardinality::MULTIPLE, $container->getCardinality());
	}
	
	public function testCreationWithValues() {
		$data = array(10, 20, 10, 30, 40, 50);
		$container = new MultipleContainer(BaseType::INTEGER, $data);
		$this->assertEquals(6, count($container));
		$this->assertEquals(BaseType::INTEGER, $container->getBaseType());
		$this->assertEquals($data, $container->getArrayCopy());
		$this->assertEquals($container[1], 20);
	}
	
	public function testCreationEmptyWrongBaseType1() {
		$this->setExpectedException('\\InvalidArgumentException');
		$container = new MultipleContainer('invalid');
	}
	
	public function testCreationEmptyWrongBaseType2() {
		$this->setExpectedException('\\InvalidArgumentException');
		$container = new MultipleContainer(14);
	}
	
	public function testCreationWithWrongValues() {
		$this->setExpectedException('\\InvalidArgumentException');
		$data = array(new Point(20, 20));
		$container = new MultipleContainer(BaseType::DURATION, $data);
	}
	
	public function testCreateFromDataModel() {
		$valueCollection = new ValueCollection();
		$valueCollection[] = new Value(new Point(10, 30), BaseType::POINT);
		$valueCollection[] = new Value(new Point(20, 40), BaseType::POINT);
		
		$container = MultipleContainer::createFromDataModel($valueCollection, BaseType::POINT);
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $container);
		$this->assertEquals(2, count($container));
		$this->assertEquals(BaseType::POINT, $container->getBaseType());
		$this->assertEquals(Cardinality::MULTIPLE, $container->getCardinality());
		$this->assertTrue($container->contains($valueCollection[0]->getValue()));
		$this->assertTrue($container->contains($valueCollection[1]->getValue()));
	}
	
	/**
	 * @dataProvider validCreateFromDataModelProvider
	 */
	public function testCreateFromDataModelValid($baseType, ValueCollection $valueCollection) {
		$container = MultipleContainer::createFromDataModel($valueCollection, $baseType);
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $container);
	}
	
	/**
	 * @dataProvider invalidCreateFromDataModelProvider
	 */
	public function testCreateFromDataModelInvalid($baseType, ValueCollection $valueCollection) {
		$this->setExpectedException('\\InvalidArgumentException');
		$container = MultipleContainer::createFromDataModel($valueCollection, $baseType);
	}
	
	public function invalidCreateFromDataModelProvider() {
		$returnValue = array();
		
		$valueCollection = new ValueCollection();
		$valueCollection[] = new Value(new Point(20, 30), BaseType::POINT);
		$valueCollection[] = new Value(10, BaseType::INTEGER);
		$returnValue[] = array(BaseType::INTEGER, $valueCollection);
		
		return $returnValue;
	}
	
	public function validCreateFromDataModelProvider() {
		$returnValue = array();
		
		$valueCollection = new ValueCollection();
		$returnValue[] = array(BaseType::DURATION, $valueCollection);
		
		$valueCollection = new ValueCollection();
		$valueCollection[] = new Value(10, BaseType::INTEGER);
		$valueCollection[] = new Value(-20, BaseType::INTEGER);
		$returnValue[] = array(BaseType::INTEGER, $valueCollection);
		
		return $returnValue;
	}
	
	public function testEquals() {
		$c1 = new MultipleContainer(BaseType::INTEGER, array(5, 4, 3, 2, 1));
		$c2 = new MultipleContainer(BaseType::INTEGER, array(1, 6, 7, 8, 5));
		$this->assertFalse($c1->equals($c2));
	}
}