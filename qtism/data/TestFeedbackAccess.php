<?php

namespace qtism\data;

use qtism\common\enums\Enumeration;

/**
 * The TestFeedBack enumeration.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestFeedbackAccess implements Enumeration {
	
	const AT_END = 0;
	
	const DURING = 1;
	
	public static function asArray() {
		return array(
			'AT_END' => self::AT_END,
			'DURING' => self::DURING		
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'atEnd':
				return self::AT_END;
			break;
			
			case 'during':
				return self::DURING;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::AT_END:
				return 'atEnd';
			break;
			
			case self::DURING:
				return 'during';
			break;
			
			default:
				return false;
			break;
		}
	}
}