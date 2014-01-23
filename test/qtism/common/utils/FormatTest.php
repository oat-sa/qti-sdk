<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\utils\Format;

class FormatTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider validIdentifierFormatProvider
	 */
	public function testValidIdentifierFormat($string) {
		$this->assertTrue(Format::isIdentifier($string));
	}
	
	/**
	 * @dataProvider invalidIdentifierFormatProvider
	 */
	public function testInvalidIdentifierFormat($string) {
		$this->assertFalse(Format::isIdentifier($string));
	}
	
	/**
	 * @dataProvider validVariableRefFormatProvider
	 */
	public function testValidVariableRefFormat($string) {
		$this->assertTrue(Format::isVariableRef($string));
	}
	
	/**
	 * @dataProvider invalidVariableRefFormatProvider
	 */
	public function testInvalidVariableRefFormat($string) {
		$this->assertFalse(Format::isVariableRef($string));
	}
	
	/**
	 * @dataProvider validCoordinatesFormatProvider
	 */
	public function testValidCoordinatesFormat($string) {
		$this->assertTrue(Format::isCoords($string));
	}
	
	/**
	 * @dataProvider invalidCoordinatesFormatProvider
	 */
	public function testInvalidCoordinatesFormat($string) {
		$this->assertFalse(Format::isCoords($string));
	}
	
	/**
	 * @dataProvider validUriFormatProvider
	 */
	public function testValidUriFormat($string) {
		$this->assertTrue(Format::isUri($string));
	}
	
	/**
	 * @dataProvider validClassFormatProvider
	 */
	public function testValidClassFormatProvider($string) {
	    $this->assertTrue(Format::isClass($string));
	}
	
	/**
	 * @dataProvider invalidClassFormatProvider
	 */
	public function testInvalidClassFormatProvider($string) {
	    $this->assertFalse(Format::isClass($string));
	}
	
	/**
	 * @dataProvider validString256FormatProvider
	 */
	public function testValidString256Provider($string) {
	    $this->assertTrue(Format::isString256($string));
	}
	
	/**
	 * @dataProvider invalidString256FormatProvider
	 */
	public function testInvalidString256Provider($string) {
	    $this->assertFalse(Format::isString256($string));
	}
	
	/**
	 * @dataProvider scale10Provider
	 */
	public function testScale10($float, $expected, $x = 'x', $precision = false) {
	    $this->assertEquals($expected, Format::scale10($float, $x, $precision));
	}
	
	public function scale10Provider() {
	    return array(
	        // No precision, no X
	        array(2, '2.000000 x 10⁰'),
	        array(25, '2.500000 x 10¹'),
	        array(-53000, '-5.300000 x 10⁴'),
	        array(6720000000, '6.720000 x 10⁹'),
	        array(672000000000, '6.720000 x 10¹¹'),
	        array(0.2, '2.000000 x 10⁻¹'),
	        array(0.00000000751, '7.510000 x 10⁻⁹'),
	                    
	        // Precision + X
	        array(2, '2.000 X 10⁰', 'X', 3),
	        array(25, '2 X 10¹', 'X', 0),
	        array(-53000, '-5.3 e 10⁴', 'e', 1),
	    );
	}
	
	public function validIdentifierFormatProvider() {
		return array(
			array('_good'),
			array('g0od'),
			array('_-goOd3'),
			array('g.0.o.d...')	,
			array('_壞壞'),
			array('myWeight1')
		);
	}
	
	public function invalidIdentifierFormatProvider() {
		return array(
			array('3bad'),
			array('.bad'),
			array('好壞好'),
			array('')
		);
	}
	
	public function validVariableRefFormatProvider() {
		return array(
			array('{_good}'),
			array('{g0od}'),
			array('{_-goOd3}'),
			array('{g.0.o.d...}')	,
			array('{_壞壞}'),
			array('{myWeight1}')
		);
	}
	
	public function invalidVariableRefFormatProvider() {
		return array(
			array('3bad'),
			array('{.bad'),
			array('好壞好}'),
			array('{}'),
			array('{{}}')
		);
	}
	
	public function validCoordinatesFormatProvider() {
		return array(
			array('30,20,60,20'),
			array('20'),
			array('200 , 100, 40')		
		);
	}
	
	public function invalidCoordinatesFormatProvider() {
		return array(
			array('30,20,x,20'),
			array('x'),
			array('invalid')
		);
	}
	
	public function validUriFormatProvider() {
		return array(
			array('http://www.taotesting.com'),
			array('../../index.html')
		);
	}
	
	public function validClassFormatProvider() {
	    return array(
	        array('a'),
	        array('my-class'),
	        array('my-class my-other-class'),
	        array('theclass')
	    );
	}
	
	public function invalidClassFormatProvider() {
	    return array(
	        array("a\tb"),
	        array(" "),
	    );
	}
	
	public function validString256FormatProvider() {
	    return array(
	        array(""),
	        array("\t\n\r"),
	        array("Hello World!"),
	        array("世界，你好！") // Hello World! 
	    );
	}
	
	public function invalidString256FormatProvider() {
	    return array(
	        array("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla non pellentesque nunc. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nunc adipiscing nisl ut risus facilisis faucibus. Morbi fermentum aliquet est et euismod. Praesent vitae adipiscing felis, ut lacinia velit. Aenean id suscipit nisi, eget feugiat tortor. Mauris eget nisi vitae mi commodo iaculis. Quisque sagittis massa in lectus semper ullamcorper. Morbi id sagittis massa. Aliquam massa dolor, pharetra nec sapien at, dignissim ultricies augue.")          
	    );
	}
}