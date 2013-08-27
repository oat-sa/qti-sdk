<?php

namespace qtism\data;

use qtism\runtime\common\ResponseVariable;
use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing runtime ResponseVariable objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseVariableCollection extends VariableCollection {

	/**
	 * Check if $value is a valid ResponseVariable object.
	 * 
	 * @throws InvalidArgumentException If $value is not a ResponseVariable object.
	 */
	protected function checkType($value) {
		if (!$value instanceof Variable) {
			$msg = "The ResponseVariableCollection class only accept ResponseVariable objects, '${value}' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}