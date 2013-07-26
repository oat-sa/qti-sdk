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