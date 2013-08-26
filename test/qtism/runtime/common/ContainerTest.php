<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\runtime\common\Container;
use qtism\common\datatypes\Pair;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Point;
use qtism\common\datatypes\Duration;

class ContainerTest extends QtiSmTestCase {

	/**
	 * A Container object reset at each test.
	 * 
	 * @var Container
	 */
	private $container;
	
	/**
	 * Get the Container object.
	 * 
	 * @return Container A Container object.
	 */
	protected function getContainer() {
		return $this->container;
	}
	
	public function setUp() {
		parent::setUp();
		$this->container = new Container();
	}
	
	public function tearDown() {
	    parent::tearDown();
	    unset($this->container);
	}
	
	/**
	 * @dataProvider validValueProvider
	 */
	public function testAddValid($value) {
		// Try to test any QTI runtime model compliant data
		// for addition in the container.
		$container = $this->getContainer();
		$container[] = $value;
		
		$this->assertTrue($container->contains($value));
	}
	
	/**
	 * @dataProvider invalidValueProvider
	 */
	public function testAddInvalid($value) {
		$container = $this->getContainer();
		
		$this->setExpectedException('\\InvalidArgumentException');
		$container[] = $value;
	}
	
	public function testIsNull() {
		$container = $this->getContainer();
		
		$this->assertTrue($container->isNull());
		
		$container[] = 1;
		$this->assertFalse($container->isNull());
	}
	
	/**
	 * @dataProvider validValueCollectionProvider
	 */
	public function testCreateFromDataModelValid(ValueCollection $valueCollection) {
		$container = Container::createFromDataModel($valueCollection);
		$this->assertInstanceOf('qtism\\runtime\\common\\Container', $container);
	}
	
	/**
	 * @dataProvider validEqualsPrimitiveProvider
	 */
	public function testEqualsPrimitiveValid($a, $b) {
		$this->assertTrue($a->equals($b));
	}
	
	/**
	 * @dataProvider invalidEqualsPrimitiveProvider
	 */
	public function testEqualsPrimitiveInvalid($a, $b) {
		$this->assertFalse($a->equals($b));
	}
	
	/**
	 * @dataProvider occurencesProvider
	 */
	public function testOccurences($container, $lookup, $expected) {
		$this->assertEquals($expected, $container->occurences($lookup));
	}
	
	public function validValueProvider() {
		return array(
			array(25),
			array(25.3),
			array(0),
			array(''),
			array('super'),
			array(true),
			array(false),
			array(new Duration('P1D')),
			array(new Point(20, 20)),
			array(new Pair('A', 'B')),
			array(new DirectedPair('C', 'D')),
			array(null)
		);
	}

	public function invalidValueProvider() {
		return array(
			array(new \DateTime()),
			array(array())	
		);
	}
	
	public function validEqualsPrimitiveProvider() {
		return array(
			array(new Container(array(true, false)), new Container(array(false, true))),
			array(new Container(array(14, 13)), new Container(array(13, 14))),
			array(new Container(array(null)), new Container(array(null))),
			array(new Container(array(0)), new Container(array(0))),
			array(new Container(array('string')), new Container(array('string'))),
			array(new Container(array(14.5)), new Container(array(14.5))),
			array(new Container(array('string1', 'string2')), new Container(array('string1', 'string2'))),
			array(new Container(), new Container()),
		);
	}
	
	public function invalidEqualsPrimitiveProvider() {
		return array(
			array(new Container(array(14)), new Container(array(13))),
			array(new Container(array(14)), new Container(array('string'))),
			array(new Container(array(null)), new Container(array(0))),
			array(new Container(), new Container(array(13))),
			array(new Container(array(true)), true),
		);
	}
	
	public function occurencesProvider() {
		return array(
			array(new Container(array(15)), 15, 1),
			array(new Container(array(14.3)), 14.3, 1),
			array(new Container(array(true)), true, 1),
			array(new Container(array(false)), false, 1),
			array(new Container(array('string')), 'string', 1),
			array(new Container(array(0)), 0, 1),
			array(new Container(array(null)), null, 1),
			array(new Container(array(15, 'string', 15)), 15, 2),
			array(new Container(array(14.3, 143, 14.3)), 14.3,  2),
			array(new Container(array(true, false, false)), 2, false),
			array(new Container(array('string', 2, 'str', 'string', 'string')), 'string', 3),
			array(new Container(array('null', null)), null, 1),
			array(new Container(array(14, 15, 16)), true, 0),
			array(new Container(array('string', 1, true, 14.3, new Point(20, 20), new Point(20, 21))), new Point(20, 20), 1)
		);
	}
	
	public function validValueCollectionProvider() {
		$returnValue = array();
		
		$valueCollection = new ValueCollection();
		$returnValue[] = array($valueCollection);
		
		$valueCollection = new ValueCollection();
		$valueCollection[] = new Value(15, BaseType::INTEGER);
		$valueCollection[] = new Value('string', BaseType::STRING);
		$valueCollection[] = new Value(true, BaseType::BOOLEAN);
		$returnValue[] = array($valueCollection);
		
		return $returnValue;
	}
	
	public function testClone() {
		$container = $this->getContainer();
		$container[] = new Point(10, 20);
		$container[] = new Duration('P2D'); // 2 days.
		$container[] = new Pair('A', 'B');
		$container[] = new DirectedPair('C', 'D');
		$container[] = 20;
		$container[] = 20.1;
		$container[] = true;
		$container[] = 'String!';
		
		$clone = clone $container;
		$this->assertFalse($clone === $container);
		$this->assertFalse($clone[0] === $container[0]);
		$this->assertFalse($clone[1] === $container[1]);
		$this->assertFalse($clone[2] === $container[2]);
		$this->assertFalse($clone[3] === $container[3]);
		$this->assertTrue($clone[4] === $container[4]);
		$this->assertTrue($clone[5] === $container[5]);
		$this->assertTrue($clone[6] === $container[6]);
		$this->assertTrue($clone[7] === $container[7]);
	}
	
	public function testContains() {
		$pair = new Pair('A', 'B');
		$container = $this->getContainer();
		$container[] = $pair;
		$this->assertTrue($container->contains(new Pair('A', 'B')));
	}
	
	/**
	 * @dataProvider toStringProvider
	 * 
	 * @param Container $container
	 * @param string $expected The expected result of a __toString() call.
	 */
	public function testToString(Container $container, $expected) {
		$this->assertEquals($expected, $container->__toString());
	}
	
	public function toStringProvider() {
		$returnValue = array();
		
		$returnValue[] = array(new Container(), '[]');
		$returnValue[] = array(new Container(array(10)), '[10]');
		$returnValue[] = array(new Container(array(true, false)), '[true; false]');
		$returnValue[] = array(new Container(array(new Duration('P2DT2S'), new Point(10, 15), new Pair('A', 'B'), new DirectedPair('C', 'D'), 'String!')), '[P2DT2S; 10 15; A B; C D; \'String!\']');
		
		return $returnValue;
	}
}