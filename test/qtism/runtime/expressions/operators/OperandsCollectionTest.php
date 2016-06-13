<?php
use qtism\common\datatypes\QtiString;

use qtism\common\datatypes\QtiFloat;

use qtism\common\datatypes\QtiBoolean;

use qtism\common\datatypes\QtiInteger;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\QtiDuration;
use qtism\runtime\expressions\operators\OperandsCollection;

class OperandsCollectionProcessorTest extends QtiSmTestCase {
	
	private $operands = null;
	
	public function setUp() {
		parent::setUp();
		$this->operands = new OperandsCollection();
	}
	
	public function tearDown() {
	    parent::tearDown();
	    unset($this->operands);
	}
	
	/**
	 * 
	 * @return OperandsCollection
	 */
	protected function getOperands() {
		return $this->operands;
	}
	
	public function testContainsNullEmpty() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->containsNull());
	}
	
	public function testContainsNullFullSingleCardinality() {
		$operands = $this->getOperands();
		$operands[] = new QtiInteger(15);
		
		$this->assertFalse($operands->containsNull());
		
		$operands[] = new QtiBoolean(true);
		$operands[] = new QtiFloat(0.4);
		$operands[] = new QtiString('string');
		$operands[] = new QtiDuration('P1D');
		$this->assertFalse($operands->containsNull());
		
		$operands[] = null;
		$this->assertTrue($operands->containsNull());
	}
	
	public function testContainsNullMixed() {
		$operands = $this->getOperands();
		$operands[] = new MultipleContainer(BaseType::FLOAT);
		
		$this->assertTrue($operands->containsNull());
		
		$operands[0][] = new QtiFloat(15.3);
		$this->assertFalse($operands->containsNull());
		
		$operands[] = new QtiString('');
		$this->assertTrue($operands->containsNull());
		
		$operands[1] = new QtiString('string!');
		$this->assertFalse($operands->containsNull());
		
		$operands[] = new RecordContainer();
		$this->assertTrue($operands->containsNull());
		
		$operands[2]['date'] = new QtiDuration('P2D');
		$this->assertFalse($operands->containsNull());
	}
	
	public function testExclusivelyNumeric() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyNumeric());
		
		$operands[] = new QtiInteger(14);
		$operands[] = new QtiFloat(15.3);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = new QtiString('');
		$this->assertFalse($operands->exclusivelyNumeric());
		unset($operands[2]);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = new QtiPoint(1, 10);
		$this->assertFalse($operands->exclusivelyNumeric());
		unset($operands[3]);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$mult = new MultipleContainer(BaseType::INTEGER);
		$operands[] = $mult;
		$this->assertFalse($operands->exclusivelyNumeric());
		$mult[] = new QtiInteger(15);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$ord = new OrderedContainer(BaseType::FLOAT);
		$operands[] = $ord;
		$this->assertFalse($operands->exclusivelyNumeric());
		$ord[] = new QtiFloat(15.5);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->exclusivelyNumeric());
		unset($operands[6]);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = new MultipleContainer(BaseType::DURATION);
		$this->assertFalse($operands->exclusivelyNumeric());
	}
	
	public function testExclusivelyInteger() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyInteger());
		
		$operands[] = new QtiInteger(14);
		$operands[] = new QtiInteger(15);
		$this->assertTrue($operands->exclusivelyInteger());
		
		$operands[] = new QtiString('');
		$this->assertFalse($operands->exclusivelyInteger());
		unset($operands[2]);
		$this->assertTrue($operands->exclusivelyInteger());
		
		$operands[] = new QtiPoint(1, 10);
		$this->assertFalse($operands->exclusivelyInteger());
		unset($operands[3]);
		$this->assertTrue($operands->exclusivelyInteger());
		
		$mult = new MultipleContainer(BaseType::INTEGER);
		$operands[] = $mult;
		$this->assertFalse($operands->exclusivelyInteger());
		$mult[] = new QtiInteger(15);
		$this->assertTrue($operands->exclusivelyInteger());
		
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->exclusivelyInteger());
		unset($operands[5]);
		$this->assertTrue($operands->exclusivelyInteger());
		
		$operands[] = new MultipleContainer(BaseType::DURATION);
		$this->assertFalse($operands->exclusivelyInteger());
	}
	
	public function testExclusivelyPoint() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyPoint());
	
		$operands[] = new QtiPoint(1, 2);
		$operands[] = new QtiPoint(3, 4);
		$this->assertTrue($operands->exclusivelyPoint());
	
		$operands[] = new QtiString('');
		$this->assertFalse($operands->exclusivelyPoint());
		unset($operands[2]);
		$this->assertTrue($operands->exclusivelyPoint());
	
		$operands[] = new QtiDuration('P1D');
		$this->assertFalse($operands->exclusivelyPoint());
		unset($operands[3]);
		$this->assertTrue($operands->exclusivelyPoint());
	
		$mult = new MultipleContainer(BaseType::POINT);
		$operands[] = $mult;
		$this->assertFalse($operands->exclusivelyPoint());
		$mult[] = new QtiPoint(1, 3);
		$this->assertTrue($operands->exclusivelyPoint());
	
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->exclusivelyPoint());
		unset($operands[5]);
		$this->assertTrue($operands->exclusivelyPoint());
	
		$operands[] = new MultipleContainer(BaseType::DURATION);
		$this->assertFalse($operands->exclusivelyPoint());
	}
	
	public function testExclusivelyDuration() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyDuration());
	
		$operands[] = new QtiDuration('P1D');
		$operands[] = new QtiDuration('P2D');
		$this->assertTrue($operands->exclusivelyDuration());
	
		$operands[] = new QtiInteger(10);
		$this->assertFalse($operands->exclusivelyDuration());
		unset($operands[2]);
		$this->assertTrue($operands->exclusivelyDuration());
	
		$operands[] = new QtiPoint(1, 2);
		$this->assertFalse($operands->exclusivelyDuration());
		unset($operands[3]);
		$this->assertTrue($operands->exclusivelyDuration());
	
		$mult = new MultipleContainer(BaseType::DURATION);
		$operands[] = $mult;
		$this->assertFalse($operands->exclusivelyDuration());
		$mult[] = new QtiDuration('P1DT2S');
		$this->assertTrue($operands->exclusivelyDuration());
	
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->exclusivelyDuration());
		unset($operands[5]);
		$this->assertTrue($operands->exclusivelyDuration());
	
		$operands[] = new MultipleContainer(BaseType::POINT);
		$this->assertFalse($operands->exclusivelyDuration());
	}
	
	public function testAnythingButRecord() {
		$operands = $this->getOperands();
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = null;
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new QtiInteger(10);
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new QtiFloat(10.11);
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new QtiPoint(1, 1);
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new QtiString('');
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new QtiString('string');
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new QtiBoolean(true);
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(10), new QtiInteger(20)));
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true)));
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->anythingButRecord());
	}
	
	public function testExclusivelyMultipleOrOrdered() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = new MultipleContainer(BaseType::BOOLEAN);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = new OrderedContainer(BaseType::POINT);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[2]);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = new QtiInteger(15);
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[3]);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = null;
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[4]);
		
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = new QtiString('');
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[5]);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = new QtiBoolean(false);
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[6]);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
	}
	
	public function testExclusivelySingleOrOrdered() {
		$operands = $this->getOperands();
		$operands[] = null;
		$this->assertTrue($operands->exclusivelySingleOrOrdered());
		
		$operands[] = new OrderedContainer(BaseType::INTEGER);
		$this->assertTrue($operands->exclusivelySingleOrOrdered());
		
		$operands[] = new QtiInteger(10);
		$this->assertTrue($operands->exclusivelySingleOrOrdered());
		
		$operands[] = new QtiBoolean(false);
		$this->assertTrue($operands->exclusivelySingleOrOrdered());
		
		$operands[] = new QtiPoint(10, 20);
		$this->assertTrue($operands->exclusivelySingleOrOrdered());
		
		$operands[] = new MultipleContainer(BaseType::INTEGER);
		$this->assertFalse($operands->exclusivelySingleOrOrdered());
	}
	
	public function testExclusivelySingleOrMultiple() {
		$operands = $this->getOperands();
		$operands[] = null;
		$this->assertTrue($operands->exclusivelySingleOrMultiple());
	
		$operands[] = new MultipleContainer(BaseType::INTEGER);
		$this->assertTrue($operands->exclusivelySingleOrMultiple());
	
		$operands[] = new QtiInteger(10);
		$this->assertTrue($operands->exclusivelySingleOrMultiple());
	
		$operands[] = new QtiBoolean(false);
		$this->assertTrue($operands->exclusivelySingleOrMultiple());
	
		$operands[] = new QtiPoint(10, 20);
		$this->assertTrue($operands->exclusivelySingleOrMultiple());
	
		$operands[] = new OrderedContainer(BaseType::INTEGER);
		$this->assertFalse($operands->exclusivelySingleOrMultiple());
	}
	
	public function testSameBaseType() {
		// If any of the values is null, false.
		$operands = new OperandsCollection(array(null, null, null));
		$this->assertFalse($operands->sameBaseType());
		
		// If any of the values is null, false.
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER), null, null));
		$this->assertFalse($operands->sameBaseType());
		
		// If any of the values is null, false.
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER), null, new QtiInteger(15)));
		$this->assertFalse($operands->sameBaseType());
		
		// If any of the values is null (an empty container is considered null), false.
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER), new QtiInteger(1), new QtiInteger(15)));
		$this->assertFalse($operands->sameBaseType());
		
		// Non-null values, all integers.
		$operands = new OperandsCollection(array(new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(15))), new QtiInteger(1), new QtiInteger(15)));
		$this->assertTrue($operands->sameBaseType());
		
		// Non-null, exclusively records.
		$operands = new OperandsCollection(array(new RecordContainer(array('a' => new QtiInteger(15))), new RecordContainer(array('b' => new QtiInteger(22)))));
		$this->assertTrue($operands->sameBaseType());
		
		// Exclusively records but considered to be null because they are empty.
		$operands = new OperandsCollection(array(new RecordContainer(), new RecordContainer()));
		$this->assertFalse($operands->sameBaseType());
		
		// Test Exclusively boolean
		$operands = new OperandsCollection(array(new QtiBoolean(true), new QtiBoolean(false)));
		$this->assertTrue($operands->sameBaseType());
		
		$operands = new Operandscollection(array(new QtiBoolean(false)));
		$this->assertTrue($operands->sameBaseType());
		
		// Test Exclusively int
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiInteger(0)));
		$this->assertTrue($operands->sameBaseType());
		
		$operands = new OperandsCollection(array(new QtiInteger(0)));
		$this->assertTrue($operands->sameBaseType());
		
		$operands = new OperandsCollection(array(new QtiInteger(10), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(10), new QtiInteger(-1), new QtiInteger(20))), new QtiInteger(5)));
		$this->assertTrue($operands->sameBaseType());
		
		// - Misc
		$operands = new Operandscollection(array(new QtiInteger(0), new QtiInteger(10), new QtiFloat(10.0)));
		$this->assertFalse($operands->sameBaseType());
	}
	
	public function testSameCardinality() {
		$operands = new OperandsCollection();
		$this->assertFalse($operands->sameCardinality());
		
		$operands = new OperandsCollection(array(null));
		$this->assertFalse($operands->sameCardinality());
		
		$operands = new OperandsCollection(array(null, new QtiInteger(10), new QtiInteger(10)));
		$this->assertFalse($operands->sameCardinality());
		
		$operands = new OperandsCollection(array(new QtiInteger(0), new QtiBoolean(false), new QtiInteger(16), new QtiBoolean(true), new QtiPoint(1, 1)));
		$this->assertTrue($operands->sameCardinality());
		
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiInteger(20), new OrderedContainer(BaseType::INTEGER)));
		$this->assertFalse($operands->sameCardinality());
	}
	
	public function testExclusivelyBoolean() {
		$operands = new OperandsCollection();
		$this->assertFalse($operands->exclusivelyBoolean());

		$operands[] = new QtiBoolean(true);
		$this->assertTrue($operands->exclusivelyBoolean());
		
		$operands[] = new QtiBoolean(false);
		$this->assertTrue($operands->exclusivelyBoolean());
		
		$container = new MultipleContainer(BaseType::BOOLEAN);
		$operands[] = $container;
		$this->assertFalse($operands->exclusivelyBoolean());
		
		$container[] = new QtiBoolean(false);
		$this->assertTrue($operands->exclusivelyBoolean());
		
		$operands = new OperandsCollection();
		$operands[] = new QtiBoolean(true);
		$this->assertTrue($operands->exclusivelyBoolean());
		$operands[] = null;
		$this->assertFalse($operands->exclusivelyBoolean());
		
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true)));
		$this->assertTrue($operands->exclusivelyBoolean());
		$operands[] = new MultipleContainer(BaseType::BOOLEAN);
		$this->assertFalse($operands->exclusivelyBoolean());
		
		$operands = new OperandsCollection();
		$operands[] = new QtiBoolean(true);
		$operands[] = new QtiBoolean(false);
		$this->assertTrue($operands->exclusivelyBoolean());
		$operands[] = new RecordContainer(array('b1' => new QtiBoolean(true), 'b2' => new QtiBoolean(false)));
		
		$operands = new OperandsCollection();
		$operands[] = new QtiBoolean(true);
		$operands[] = new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(true)));
		$this->assertTrue($operands->exclusivelyBoolean());
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->exclusivelyBoolean());
	}
	
	public function testExclusivelyRecord() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyRecord());
		
		$rec = new RecordContainer();
		$operands[] = $rec;
		$this->assertTrue($operands->exclusivelyRecord());
		
		$rec['A'] = new QtiInteger(1);
		$this->assertTrue($operands->exclusivelyRecord());
		
		$operands[] = null;
		$this->assertFalse($operands->exclusivelyRecord());
		
		$operands->reset();
		$operands[] = $rec;
		$this->assertTrue($operands->exclusivelyRecord());
		
		$operands[] = new QtiInteger(10);
		$this->assertFalse($operands->exclusivelyRecord());
		
		$operands->reset();
		$operands[] = $rec;
		$operands[] = new QtiString('String!');
		$this->assertFalse($operands->exclusivelyRecord());
		
	}
	
	public function testExclusivelyOrdered() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyOrdered());
	
		$mult = new OrderedContainer(BaseType::INTEGER);
		$operands[] = $mult;
		$this->assertTrue($operands->exclusivelyOrdered());
	
		$mult[] = new QtiInteger(-10);
		$this->assertTrue($operands->exclusivelyOrdered());
	
		$operands[] = null;
		$this->assertFalse($operands->exclusivelyOrdered());
	
		$operands->reset();
		$operands[] = $mult;
		$this->assertTrue($operands->exclusivelyOrdered());
	
		$operands[] = new QtiInteger(10);
		$this->assertFalse($operands->exclusivelyOrdered());
	
		$operands->reset();
		$operands[] = $mult;
		$operands[] = new QtiString('String!');
		$this->assertFalse($operands->exclusivelyOrdered());
		
		$operands->reset();
		$operands[] = $mult;
		$operands[] = new MultipleContainer(BaseType::URI);
		$this->assertFalse($operands->exclusivelyOrdered());
	}
}
