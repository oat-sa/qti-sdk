<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\BaseType;

class OperatorBaseType extends BaseType {
	
	/**
	 * Express that the operands can have any BaseType from the BaseType enumeration and
	 * can be different.
	 * 
	 * @var int
	 */
	const ANY = 12;
	
	/**
	 * Express that all the operands must have the same
	 * baseType.
	 * 
	 * @var int
	 */
	const SAME = 13;
	
	public static function asArray() {
		$values = BaseType::asArray();
		$values['ANY'] = self::ANY;
		$values['SAME'] = self::SAME;
		
		return $values;
	}
}