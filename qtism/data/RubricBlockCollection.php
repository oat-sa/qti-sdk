<?php

namespace qtism\data;

use InvalidArgumentException as InvalidArgumentException;
use qtism\common\collections\AbstractCollection;

/**
 * A collection that aims at storing RubrickBlock objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RubricBlockCollection extends AbstractCollection {

	/**
	 * Check if $value is a RubricBlock object.
	 * 
	 * @throws InvalidArgumentException If $value is not a RubricBlock object.
	 */
	protected function checkType($value) {
		if (!$value instanceof RubricBlock) {
			$msg = "RubricBlockCollection class only accept RubricBlock objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}