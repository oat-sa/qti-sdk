<?php

namespace qtism\data;

use qtism\common\enums\Enumeration;

/**
 * The ShowHide enumeration.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ShowHide implements Enumeration {
	
	const SHOW = 0;
	
	const HIDE = 1;
	
	public static function asArray() {
		return array(
			'SHOW' => self::SHOW,
			'HIDE' => self::HIDE		
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'show':
				return self::SHOW;
			break;
			
			case 'hide':
				return self::HIDE;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::SHOW:
				return 'show';
			break;
			
			case self::HIDE:
				return 'hide';
			break;
			
			default:
				return false;
			break;
		}
	}
}