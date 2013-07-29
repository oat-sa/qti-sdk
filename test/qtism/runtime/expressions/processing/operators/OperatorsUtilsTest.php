<?php
require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\processing\operators\Utils as OperatorsUtils;

class OperatorsUtilsTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider gcdProvider
	 * 
	 * @param integer $a
	 * @param integer $b
	 * @param integer $expected
	 */
	public function testGcd($a, $b, $expected) {
		$result = OperatorsUtils::gcd($a, $b);
		$this->assertInternalType('integer', $result);
		$this->assertSame($expected, $result);
	}
	
	/**
	 * @dataProvider lcmProvider
	 * 
	 * @param integer $a
	 * @param integer $b
	 * @param integer $expected
	 */
	public function testLcm($a, $b, $expected) {
		$result = OperatorsUtils::lcm($a, $b);
		$this->assertInternalType('integer', $result);
		$this->assertSame($expected, $expected);
	}
	
	/**
	 * @dataProvider getPrecedingBackslashesCountProvider
	 *
	 * @param string $string
	 * @param integer $offset
	 * @param integer $expected Expected preceding backslashes count.
	 */
	public function testGetPrecedingBackslashesCount($string, $offset, $expected) {
		$this->assertSame($expected, OperatorsUtils::getPrecedingBackslashesCount($string, $offset));
	}
	
	/**
	 * @dataProvider pregAddDelimiterProvider
	 *
	 * @param string $string
	 * @param string $expected
	 */
	public function testPregAddDelimiter($string, $expected) {
		$this->assertSame($expected, OperatorsUtils::pregAddDelimiter($string));
	}
	
	/**
	 * @dataProvider escapeSymbolsProvider
	 * 
	 * @param string $string
	 * @param array|string $symbols
	 * @param string $expected
	 */
	public function testEscapeSymbols($string, $symbols, $expected) {
		$this->assertSame($expected, OperatorsUtils::escapeSymbols($string, $symbols));
	}
	
	public function pregAddDelimiterProvider() {
		return array(
				array('', '//'),
				array('test', '/test/'),
				array('te/st', '/te\\/st/'),
				array('/', '/\\//'),
				array('/test', '/\\/test/'),
				array('test/', '/test\\//'),
				array('te/st is /test/', '/te\\/st is \\/test\\//'),
				array('te\\/st', '/te\\/st/'),
				array('te\\\\/st', '/te\\\\\\/st/'),
				array('te\\\\\\\\/st', '/te\\\\\\\\\\/st/'),
				array('\d{1,2}', '/\d{1,2}/')
		);
	}
	
	public function escapeSymbolsProvider() {
		return array(
			array('10$ are 10$', array('$', '^'), '10\\$ are 10\\$'),
			array('$$$Jackpot$$$', '$', '\\$\\$\\$Jackpot\\$\\$\\$'),
			array('^exp$', array('$', '^'), '\\^exp\\$')
		);
	}
	
	public function getPrecedingBackslashesCountProvider() {
		return array(
				array('', 0, 0),
				array('string!', 0, 0),
				array('string!', 10, 0),
				array('string!', 6, 0),
				array('string!', -20, 0),
				array('\\a', 1, 1),
				array('\\\\a', 2, 2),
				array('\\abc\\\\\\d', 7, 3)
		);
	}
	
	public function gcdProvider() {
		return array(
			array(60, 330, 30),
			array(256, 1024, 256),
			array(456, 3698, 2),
			array(25, 0, 25),
			array(0, 25, 25),
			array(0, 0, 0)
		);
	}
	
	public function lcmProvider() {
		return array(
			array(4, 3, 12),
			array(0, 3, 0),
			array(3, 0, 0),
			array(0, 0, 0),
			array(330, -65, 4290)
		);
	}
}