<?php

namespace qtism\data\expressions;

use qtism\common\enums\Enumeration;

/**
 * The class of Mathematical constants provided by QTI.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MathEnumeration implements Enumeration {
	
	/**
	 * From IMS QTI:
	 * 
	 * The number π, the ratio of the circumference of a circle to its diameter.
	 * 
	 * @var float
	 */
	const PI = 0;
	
	/**
	 * From IMS QTI:
	 * 
	 * The number e, exp(1).
	 * 
	 * @var float
	 */
	const E = 1;
	
	public static function asArray() {
		return array(
			'PI' => self::PI,
			'E' => self::E		
		);
	}

	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::PI:
				return 'pi';
			break;
			
			case self::E:
				return 'e';
			break;
		}
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'pi':
				return self::PI;
			break;
			
			case 'e':
				return self::E;
			break;
		}
	}
}