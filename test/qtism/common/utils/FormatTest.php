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