<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Enumeration;

class ToleranceMode implements Enumeration {
	
	const EXACT = 0;
	
	const ABSOLUTE = 1;
	
	const RELATIVE = 2;
	
	public static function asArray() {
		return array(
			'EXACT' => self::EXACT,
			'ABSOLUTE' => self::ABSOLUTE,
			'RELATIVE' => self::RELATIVE		
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'exact':
				return self::EXACT;
			break;
			
			case 'absolute':
				return self::ABSOLUTE;
			break;
			
			case 'relative':
				return self::RELATIVE;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::EXACT:
				return 'exact';
			break;
			
			case self::ABSOLUTE:
				return 'absolute';
			break;
			
			case self::RELATIVE:
				return 'relative';
			break;
			
			default:
				return false;
			break;
		}
	}
}