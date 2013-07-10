<?php

namespace qtism\data\state;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of Value objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ValueCollection extends AbstractCollection {
	
	/**
	 * Check if a given $value is an instance of Value.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of Value.
	 */
	protected function checkType($value) {
		if (!$value instanceof Value) {
			$msg = "ValueCollection only accepts to store Value objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}