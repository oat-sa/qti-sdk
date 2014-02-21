<?php

use qtism\common\Comparable;

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
	
	/**
	 * @dataProvider isValidVariableIdentifierProvider
	 * 
	 * @param string $string
	 * @param boolean $expected
	 */
	public function testIsValidVariableIdentifier($string, $expected) {
		$this->assertSame($expected, Utils::isValidVariableIdentifier($string));
	}
	
	/**
	 * @dataProvider juggleProvider
	 * 
	 * @param mixed $value The value to be juggled.
	 * @param integer $targetBaseType The target baseType.
	 * @param boolean $jugglingExpected whether or not juggling transformation is expected.
	 * @param mixed $targetValue The expected juggled value If $jugglingExpected === true.
	 */
	public function testJuggle($value, $targetBaseType, $jugglingExpected, $targetValue = null) {
	    $juggled = Utils::juggle($value, $targetBaseType);
	    
	    if ($jugglingExpected === true) {
	        if ($targetValue instanceof Comparable) {
	            $this->assertTrue($targetValue->equals($juggled));
	        }
	        else if ($targetBaseType === BaseType::FLOAT) {
	            $this->assertTrue(round($targetValue, 3) === round($juggled, 3));
	        }
	        else {
	            $this->assertTrue($targetValue === $juggled);
	        }
	    }
	    else {
	        $this->assertTrue($juggled === $value);
	    }
	}
	
	public function juggleProvider() {
	    /*
	     * Cross product approach:
	     * 
	     * XType vs identifier, boolean, integer, float, string, point, pair, directedPair, duration, (file), uri, intOrIdentifier
	     */
	    return array(
	        // Scalar - identifier
	        array('identifier', BaseType::IDENTIFIER, false),
	        array('identifier', BaseType::BOOLEAN, false),
	        array('identifier', BaseType::INTEGER, false),
	        array('identifier', BaseType::FLOAT, false),
	        array('identifier', BaseType::STRING, false),
	        array('identifier', BaseType::POINT, false),
	        array('identifier', BaseType::PAIR, false),
	        array('identifier', BaseType::DIRECTED_PAIR, false),
	        array('identifier', BaseType::DURATION, false),
	        array('identifier', BaseType::URI, false),
	        array('identifier', BaseType::INT_OR_IDENTIFIER, false),
	                    
	        // Scalar - boolean
	        array(true, BaseType::IDENTIFIER, false),
	        array(true, BaseType::BOOLEAN, false),
	        array(true, BaseType::INTEGER, false),
	        array(true, BaseType::FLOAT, false),
	        array(true, BaseType::STRING, false),
	        array(true, BaseType::POINT, false),
	        array(true, BaseType::PAIR, false),
	        array(true, BaseType::DIRECTED_PAIR, false),
	        array(true, BaseType::DURATION, false),
	        array(true, BaseType::URI, false),
	        array(true, BaseType::INT_OR_IDENTIFIER, false),

	        // Scalar - integer
	        array(25, BaseType::IDENTIFIER, false),
	        array(25, BaseType::BOOLEAN, false),
	        array(25, BaseType::INTEGER, false),
	        array(25, BaseType::FLOAT, true, 25.0),
	        array(25, BaseType::STRING, false),
	        array(25, BaseType::POINT, false),
	        array(25, BaseType::PAIR, false),
	        array(25, BaseType::DIRECTED_PAIR, false),
	        array(25, BaseType::DURATION, false),
	        array(25, BaseType::URI, false),
	        array(25, BaseType::INT_OR_IDENTIFIER, false),

	        // Scalar - float
	        array(25.0, BaseType::IDENTIFIER, false),
	        array(25.0, BaseType::BOOLEAN, false),
	        array(25.0, BaseType::INTEGER, true, 25),
	        array(25.0, BaseType::FLOAT, false),
	        array(25.0, BaseType::STRING, false),
	        array(25.0, BaseType::POINT, false),
	        array(25.0, BaseType::PAIR, false),
	        array(25.0, BaseType::DIRECTED_PAIR, false),
	        array(25.0, BaseType::DURATION, false),
	        array(25.0, BaseType::URI, false),
	        array(25.0, BaseType::INT_OR_IDENTIFIER, false),

	        // Scalar - string
            array('string', BaseType::IDENTIFIER, false),
            array('string', BaseType::BOOLEAN, false),
            array('string', BaseType::INTEGER, false),
            array('string', BaseType::FLOAT, false),
            array('string', BaseType::STRING, false),
            array('string', BaseType::POINT, false),
            array('string', BaseType::PAIR, false),
            array('string', BaseType::DIRECTED_PAIR, false),
            array('string', BaseType::DURATION, false),
            array('string', BaseType::URI, false),
            array('string', BaseType::INT_OR_IDENTIFIER, false),
	                    
	        // Scalar - point
            array(new Point(10, 10), BaseType::IDENTIFIER, false),
            array(new Point(10, 10), BaseType::BOOLEAN, false),
            array(new Point(10, 10), BaseType::INTEGER, false),
            array(new Point(10, 10), BaseType::FLOAT, false),
            array(new Point(10, 10), BaseType::STRING, false),
            array(new Point(10, 10), BaseType::POINT, false),
            array(new Point(10, 10), BaseType::PAIR, false),
            array(new Point(10, 10), BaseType::DIRECTED_PAIR, false),
            array(new Point(10, 10), BaseType::DURATION, false),
            array(new Point(10, 10), BaseType::URI, false),
            array(new Point(10, 10), BaseType::INT_OR_IDENTIFIER, false),
	                    
	        // Scalar - pair
            array(new Pair('A', 'B'), BaseType::IDENTIFIER, false),
            array(new Pair('A', 'B'), BaseType::BOOLEAN, false),
            array(new Pair('A', 'B'), BaseType::INTEGER, false),
            array(new Pair('A', 'B'), BaseType::FLOAT, false),
            array(new Pair('A', 'B'), BaseType::STRING, false),
            array(new Pair('A', 'B'), BaseType::POINT, false),
            array(new Pair('A', 'B'), BaseType::PAIR, false),
            array(new Pair('A', 'B'), BaseType::DIRECTED_PAIR, false),
            array(new Pair('A', 'B'), BaseType::DURATION, false),
            array(new Pair('A', 'B'), BaseType::URI, false),
            array(new Pair('A', 'B'), BaseType::INT_OR_IDENTIFIER, false),

	        // Scalar - directedPair
            array(new DirectedPair('A', 'B'), BaseType::IDENTIFIER, false),
            array(new DirectedPair('A', 'B'), BaseType::BOOLEAN, false),
            array(new DirectedPair('A', 'B'), BaseType::INTEGER, false),
            array(new DirectedPair('A', 'B'), BaseType::FLOAT, false),
            array(new DirectedPair('A', 'B'), BaseType::STRING, false),
            array(new DirectedPair('A', 'B'), BaseType::POINT, false),
            array(new DirectedPair('A', 'B'), BaseType::PAIR, false),
            array(new DirectedPair('A', 'B'), BaseType::DIRECTED_PAIR, false),
            array(new DirectedPair('A', 'B'), BaseType::DURATION, false),
            array(new DirectedPair('A', 'B'), BaseType::URI, false),
            array(new DirectedPair('A', 'B'), BaseType::INT_OR_IDENTIFIER, false),

	        // Scalar - duration
            array(new Duration('PT1S'), BaseType::IDENTIFIER, false),
            array(new Duration('PT1S'), BaseType::BOOLEAN, false),
            array(new Duration('PT1S'), BaseType::INTEGER, false),
            array(new Duration('PT1S'), BaseType::FLOAT, false),
            array(new Duration('PT1S'), BaseType::STRING, false),
            array(new Duration('PT1S'), BaseType::POINT, false),
            array(new Duration('PT1S'), BaseType::PAIR, false),
            array(new Duration('PT1S'), BaseType::DIRECTED_PAIR, false),
            array(new Duration('PT1S'), BaseType::DURATION, false),
            array(new Duration('PT1S'), BaseType::URI, false),
            array(new Duration('PT1S'), BaseType::INT_OR_IDENTIFIER, false),

	        // Scalar - uri
            array('http://www.taotesting.com', BaseType::IDENTIFIER, false),
            array('http://www.taotesting.com', BaseType::BOOLEAN, false),
            array('http://www.taotesting.com', BaseType::INTEGER, false),
            array('http://www.taotesting.com', BaseType::FLOAT, false),
            array('http://www.taotesting.com', BaseType::STRING, false),
            array('http://www.taotesting.com', BaseType::POINT, false),
            array('http://www.taotesting.com', BaseType::PAIR, false),
            array('http://www.taotesting.com', BaseType::DIRECTED_PAIR, false),
            array('http://www.taotesting.com', BaseType::DURATION, false),
            array('http://www.taotesting.com', BaseType::URI, false),
            array('http://www.taotesting.com', BaseType::INT_OR_IDENTIFIER, false),

	        // Scalar - intOrIdentifier
            array(9, BaseType::IDENTIFIER, false),
            array('identifier', BaseType::BOOLEAN, false),
            array('identifier', BaseType::INTEGER, false),
            array(25, BaseType::FLOAT, true, 25.0),
            array('identifier', BaseType::STRING, false),
            array(-40, BaseType::POINT, false),
            array('identifier', BaseType::PAIR, false),
            array(-45, BaseType::DIRECTED_PAIR, false),
            array('identifier', BaseType::DURATION, false),
            array(-365, BaseType::URI, false),
            array('identifier', BaseType::INT_OR_IDENTIFIER, false),
	                    
	        // Scalar - NULL
	        array(null, BaseType::IDENTIFIER, false),
	        array(null, BaseType::BOOLEAN, false),
	        array(null, BaseType::INTEGER, false),
	        array(null, BaseType::FLOAT, false),
	        array(null, BaseType::STRING, false),
	        array(null, BaseType::POINT, false),
	        array(null, BaseType::PAIR, false),
	        array(null, BaseType::DIRECTED_PAIR, false),
	        array(null, BaseType::DURATION, false),
	        array(null, BaseType::URI, false),
	        array(null, BaseType::INT_OR_IDENTIFIER, false),
	                    
	        // Multiple - identifier
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::IDENTIFIER, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::BOOLEAN, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::INTEGER, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::FLOAT, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::STRING, true, new MultipleContainer(BaseType::STRING, array('id1', 'id2', 'id3'))),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::POINT, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::PAIR, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::DIRECTED_PAIR, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::DURATION, false),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::URI, true, new MultipleContainer(BaseType::URI, array('id1', 'id2', 'id3'))),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - boolean
	        array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::URI, false),
            array(new MultipleContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - integer
	        array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::FLOAT, true, new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0))),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::URI, false),
            array(new MultipleContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - float
	        array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::INTEGER, true, new MultipleContainer(BaseType::INTEGER, array(1, 2, 3))),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::URI, false),
            array(new MultipleContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - string
	        array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::IDENTIFIER, true, new MultipleContainer(BaseType::IDENTIFIER, array('str1', 'str2', 'str3'))),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::URI, true, new MultipleContainer(BaseType::URI, array('str1', 'str2', 'str3'))),
            array(new MultipleContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - point
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::URI, false),
            array(new MultipleContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - pair
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::URI, false),
            array(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - directedPair
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::URI, false),
            array(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - duration
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::URI, false),
            array(new MultipleContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::INT_OR_IDENTIFIER, false),
            
            // Multiple - uri
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::IDENTIFIER, true, new MultipleContainer(BaseType::IDENTIFIER, array('http://host1', 'http://host2', 'http://host3'))),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::STRING, true, new MultipleContainer(BaseType::STRING, array('http://host1', 'http://host2', 'http://host3'))),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::URI, false),
            array(new MultipleContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::INT_OR_IDENTIFIER, false),
	                    
            // Multiple - intOrIdentifier
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::IDENTIFIER, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::BOOLEAN, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::INTEGER, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::FLOAT, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::STRING, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::POINT, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::PAIR, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::DIRECTED_PAIR, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::DURATION, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::URI, false),
            array(new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::INT_OR_IDENTIFIER, false),

            // Ordered - identifier
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::STRING, true, new OrderedContainer(BaseType::STRING, array('id1', 'id2', 'id3'))),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::URI, true, new OrderedContainer(BaseType::URI, array('id1', 'id2', 'id3'))),
            array(new OrderedContainer(BaseType::IDENTIFIER, array('id1', 'id2', 'id3')), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - boolean
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::URI, false),
            array(new OrderedContainer(BaseType::BOOLEAN, array(true, false, true)), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - integer
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::FLOAT, true, new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0))),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::URI, false),
            array(new OrderedContainer(BaseType::INTEGER, array(1, 2, 3)), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - float
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::INTEGER, true, new OrderedContainer(BaseType::INTEGER, array(1, 2, 3))),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::URI, false),
            array(new OrderedContainer(BaseType::FLOAT, array(1.0, 2.0, 3.0)), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - string
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::IDENTIFIER, true, new OrderedContainer(BaseType::IDENTIFIER, array('str1', 'str2', 'str3'))),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::URI, true, new OrderedContainer(BaseType::URI, array('str1', 'str2', 'str3'))),
            array(new OrderedContainer(BaseType::STRING, array('str1', 'str2', 'str3')), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - point
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::URI, false),
            array(new OrderedContainer(BaseType::POINT, array(new Point(1, 1), new Point(2, 3), new Point(4, 5))), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - pair
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::URI, false),
            array(new OrderedContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - directedPair
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::URI, false),
            array(new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - duration
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::URI, false),
            array(new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S'), new Duration('PT2S'), new Duration('PT3S'))), BaseType::INT_OR_IDENTIFIER, false),
            
            // Ordered - uri
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::IDENTIFIER, true, new OrderedContainer(BaseType::IDENTIFIER, array('http://host1', 'http://host2', 'http://host3'))),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::STRING, true, new OrderedContainer(BaseType::STRING, array('http://host1', 'http://host2', 'http://host3'))),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::URI, false),
            array(new OrderedContainer(BaseType::URI, array('http://host1', 'http://host2', 'http://host3')), BaseType::INT_OR_IDENTIFIER, false),
             
            // Ordered - intOrIdentifier
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::IDENTIFIER, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::BOOLEAN, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::INTEGER, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::FLOAT, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::STRING, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::POINT, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::PAIR, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::DIRECTED_PAIR, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::DURATION, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::URI, false),
            array(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1, 'a1', 2)), BaseType::INT_OR_IDENTIFIER, false),
	                    
	        // Record
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::IDENTIFIER, false),
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::BOOLEAN, false),
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::INTEGER, false),
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::FLOAT, false),
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::STRING, false),
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::POINT, false),
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::PAIR, false),
	        array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::DIRECTED_PAIR, false),
            array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::DURATION, false),
            array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::URI, false),
            array(new RecordContainer(array('a' => 'a', 'b' => 2, 'c' => 3.0)), BaseType::INT_OR_IDENTIFIER, false),

	        // Miscellaneous
	        array(new MultipleContainer(BaseType::INTEGER, array(1, null, 3)), BaseType::FLOAT, true, new MultipleContainer(BaseType::FLOAT, array(1.0, null, 3.0))),
	        array(new OrderedContainer(BaseType::FLOAT, array(1.0, null, 3.0)), BaseType::INTEGER, true, new OrderedContainer(BaseType::INTEGER, array(1, null, 3))),
	        array(new MultipleContainer(BaseType::IDENTIFIER, array('id1', null, 'id3')), BaseType::STRING, true, new MultipleContainer(BaseType::STRING, array('id1', null, 'id3'))),
	    );
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
	
	public function isValidVariableIdentifierProvider() {
		return array(
			array('Q01', true),
			array('Q_01', true),
			array('Q-01', true),
			array('Q*01', false),
			array('q01', true),
			array('_Q01', false),
			array('', false),
			array(1337, false),
			array('Q01.1', true),
			array('Q01.1.SCORE', true),
			array('Q01.999.SCORE', true),
			array('Q01.A.SCORE', false),
			array('Qxx.12.', false),
			array('Q-2.', false),
			array('934.9.SCORE', false),
			array('A34.10.S-C-O', true),
			array('999', false),
			array('Q01.1.SCORE.MAX', false),
			array('Q 01', false),
			array('Q01 . SCORE', false),
			array('Q_01.SCORE', true),
			array('Q01.0.SCORE', false), // non positive sequence number -> false
			array('Q01.09.SCORE', false) // prefixing sequence by zero not allowed.
		);
	}
}