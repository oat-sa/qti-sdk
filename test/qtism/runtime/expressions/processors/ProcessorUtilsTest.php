<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\runtime\expressions\processing\Utils;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;

class ProcessorUtilsTest extends QtiSmTestCase {

	/**
	 * @dataProvider numericValidProvider
	 */
	public function testIsNumericValid($value) {
		$this->assertTrue(Utils::isNumeric($value));
	}
	
	/**
	 * @dataProvider numericInvalidProvider
	 */
	public function testIsNumericInvalid($value) {
		$this->assertFalse(Utils::isNumeric($value));
	}
	
	/**
	 * @dataProvider sanitizeVariableRefValidProvider
	 */
	public function testSanitizeVariableRefValid($value, $expected) {
		$ref = $this->assertEquals(Utils::sanitizeVariableRef($value), $expected);
	}
	
	/**
	 * @dataProvider sanitizeVariableRefInvalidProvider
	 */
	public function testSanitizeVariableRefInvalid($value) {
		$this->setExpectedException('\\InvalidArgumentException');
		$ref = Utils::sanitizeVariableRef($value);
	}
	
	public function numericValidProvider() {
		$returnValue = array(
			array(15),
			array(15.1)	,
			array(0),
			array(-0),
			array(-15),
			array(-15.1)
		);
		
		$returnValue[] = array(new MultipleContainer(BaseType::INTEGER, array(15, -2)));
		$returnValue[] = array(new OrderedContainer(BaseType::FLOAT, array(-1.434, 1423.2, 3.0)));
		
		return $returnValue;
	}
	
	public function numericInvalidProvider() {
		$returnValue = array(
			array(true),
			array(false),
			array('string'),
			array(new \stdClass())	
		);
		
		$returnValue[] = array(new MultipleContainer(BaseType::BOOLEAN, array(true, false)));
		return $returnValue;
	}
	
	public function sanitizeVariableRefValidProvider() {
		return array(
			array('variableRef', 'variableRef'),
			array('{variableRef', 'variableRef'),
			array('variableRef}', 'variableRef'),
			array('{variableRef}', 'variableRef'),
			array('{{variableRef}}', 'variableRef'),
			array('', ''),
			array('{}', '')	
		);
	}
	
	public function sanitizeVariableRefInvalidProvider() {
		return array(
			array(new \stdClass()),
			array(14),
			array(0),
			array(false)		
		);
	}
}