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
}