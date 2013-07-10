<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Cardinality;

class OperatorCardinality extends Cardinality {
	
	/**
	 * Express that all the expressions involved in an operator have
	 * the same cardinality. 
	 * 
	 * @var integer
	 */
	const SAME = 4;
	
	/**
	 * Express that all the expressions involved in an operator may
	 * have any cardinality.
	 * 
	 * @var integer
	 */
	const ANY = 5;
	
	public static function asArray() {
		$values = Cardinality::asArray();
		$values['SAME'] = self::SAME;
		$values['ANY'] = self::ANY;
		
		return $values;
	}
}