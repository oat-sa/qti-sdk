<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\Point;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\Duration;
use qtism\runtime\expressions\processing\OperandsCollection;

class OperandsCollectionProcessorTest extends QtiSmTestCase {
	
	private $operands = null;
	
	public function setUp() {
		parent::setUp();
		$this->operands = new OperandsCollection();
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
		$operands[] = 15;
		
		$this->assertFalse($operands->containsNull());
		
		$operands[] = true;
		$operands[] = 0.4;
		$operands[] = 'string';
		$operands[] = new Duration('P1D');
		$this->assertFalse($operands->containsNull());
		
		$operands[] = null;
		$this->assertTrue($operands->containsNull());
	}
	
	public function testContainsNullMixed() {
		$operands = $this->getOperands();
		$operands[] = new MultipleContainer(BaseType::FLOAT);
		
		$this->assertTrue($operands->containsNull());
		
		$operands[0][] = 15.3;
		$this->assertFalse($operands->containsNull());
		
		$operands[] = '';
		$this->assertTrue($operands->containsNull());
		
		$operands[1] = 'string!';
		$this->assertFalse($operands->containsNull());
		
		$operands[] = new RecordContainer();
		$this->assertTrue($operands->containsNull());
		
		$operands[2]['date'] = new Duration('P2D');
		$this->assertFalse($operands->containsNull());
	}
	
	public function testExclusivelyNumeric() {
		$operands = $this->getOperands();
		$this->assertFalse($operands->exclusivelyNumeric());
		
		$operands[] = 14;
		$operands[] = 15.3;
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = '';
		$this->assertFalse($operands->exclusivelyNumeric());
		unset($operands[2]);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = new Point(1, 10);
		$this->assertFalse($operands->exclusivelyNumeric());
		unset($operands[3]);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$mult = new MultipleContainer(BaseType::INTEGER);
		$operands[] = $mult;
		$this->assertFalse($operands->exclusivelyNumeric());
		$mult[] = 15;
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$ord = new OrderedContainer(BaseType::FLOAT);
		$operands[] = $ord;
		$this->assertFalse($operands->exclusivelyNumeric());
		$ord[] = 15.5;
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = new RecordContainer();
		$this->assertFalse($operands->exclusivelyNumeric());
		unset($operands[6]);
		$this->assertTrue($operands->exclusivelyNumeric());
		
		$operands[] = new MultipleContainer(BaseType::DURATION);
		$this->assertFalse($operands->exclusivelyNumeric());
	}
	
	public function testAnythingButRecord() {
		$operands = $this->getOperands();
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = null;
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = 10;
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = 10.11;
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new Point(1, 1);
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = '';
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = 'string';
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = true;
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(10, 20));
		$this->assertTrue($operands->anythingButRecord());
		
		$operands[] = new OrderedContainer(BaseType::BOOLEAN, array(true, false, true));
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
		
		$operands[] = 15;
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[3]);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = null;
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[4]);
		
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = '';
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[5]);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
		
		$operands[] = false;
		$this->assertFalse($operands->exclusivelyMultipleOrOrdered());
		unset($operands[6]);
		$this->assertTrue($operands->exclusivelyMultipleOrOrdered());
	}
}