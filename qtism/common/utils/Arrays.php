<?php

namespace qtism\common\utils;

/**
 * A utility class focusing on arrays.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Arrays {
	
	public static function isAssoc(array $array) {
		return array_keys($array) !== range(0, count($array) - 1);
	}
}