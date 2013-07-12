<?php

use qtism\runtime\common\Container;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Pair;
use qtism\common\datatypes\Point;
use qtism\runtime\common\OrderedContainer;
use qtism\common\datatypes\Duration;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\Utils;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');


class RuntimeUtilsTest extends QtiSmTestCase {

	/**
	 * @dataProvider inferBaseTypeProvider
	 */
	public function testInferBaseType($value, $expectedBaseType) {
		$this->assertTrue(Utils::inferBaseType($value) === $expectedBaseType);
	}
	
	/**
	 * @dataProvider inferCardinalityProvider
	 */
	public function testInferCardinality($value, $expectedCardinality) {
		$this->assertTrue(Utils::inferCardinality($value) === $expectedCardinality);
	}
	
	public function inferBaseTypeProvider() {
		$returnValue = array();
		
		$returnValue[] = array(new RecordContainer(), false);
		$returnValue[] = array(new RecordContainer(array('a' => 1, 'b' => 2)), false);
		$returnValue[] = array(null, false);
		$returnValue[] = array('', BaseType::STRING);
		$returnValue[] = array('String!', BaseType::STRING);
		$returnValue[] = array(false, BaseType::BOOLEAN);
		$returnValue[] = array(0, BaseType::INTEGER);
		$returnValue[] = array(0.0, BaseType::FLOAT);
		$returnValue[] = array(new MultipleContainer(BaseType::DURATION), BaseType::DURATION);
		$returnValue[] = array(new OrderedContainer(BaseType::BOOLEAN), BaseType::BOOLEAN);
		$returnValue[] = array(new Duration('P1D'), BaseType::DURATION);
		$returnValue[] = array(new Point(1, 1), BaseType::POINT);
		$returnValue[] = array(new Pair('A', 'B'), BaseType::PAIR);
		$returnValue[] = array(new DirectedPair('A', 'B'), BaseType::DIRECTED_PAIR);
		$returnValue[] = array(new \StdClass(), false);
		$returnValue[] = array(new Container(), false);
		
		return $returnValue;
	}
	
	public function inferCardinalityProvider() {
		$returnValue = array();
		
		$returnValue[] = array(new RecordContainer(), Cardinality::RECORD);
		$returnValue[] = array(new MultipleContainer(BaseType::INTEGER), Cardinality::MULTIPLE);
		$returnValue[] = array(new OrderedContainer(BaseType::DURATION), Cardinality::ORDERED);
		$returnValue[] = array(new \stdClass(), false);
		$returnValue[] = array(null, false);
		$returnValue[] = array('', Cardinality::SINGLE);
		$returnValue[] = array('String!', Cardinality::SINGLE);
		$returnValue[] = array(0, Cardinality::SINGLE);
		$returnValue[] = array(0.0, Cardinality::SINGLE);
		$returnValue[] = array(false, Cardinality::SINGLE);
		$returnValue[] = array(new Point(1, 1), Cardinality::SINGLE);
		$returnValue[] = array(new Pair('A', 'B'), Cardinality::SINGLE);
		$returnValue[] = array(new DirectedPair('A', 'B'), Cardinality::SINGLE);
		$returnValue[] = array(new Duration('P1D'), Cardinality::SINGLE);
		
		return $returnValue;
	}
}