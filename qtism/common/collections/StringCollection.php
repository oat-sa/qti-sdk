<?php

namespace qtism\common\collections;

use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing string values.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StringCollection extends AbstractCollection {

	/**
	 * Check if $value is a valid string.
	 * 
	 * @throws InvalidArgumentException If $value is not a valid string.
	 */
	protected function checkType($value) {
		if (!is_string($value)) {
			$msg = "StringCollection class only accept string values, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}