<?php

namespace qtism\common\collections;

use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing integer values.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IntegerCollection extends AbstractCollection {

	/**
	 * Check if $value is a valid integer.
	 * 
	 * @throws InvalidArgumentException If $value is not a valid integer.
	 */
	protected function checkType($value) {
		if (!is_int($value)) {
			$msg = "IntegerCollection class only accept integer values, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}