<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of ResponseElseIf objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseElseIfCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of ResponseElseIf.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of ResponseElseIf.
	 */
	protected function checkType($value) {
		if (!$value instanceof ResponseElseIf) {
			$msg = "ResponseElseIfCollection only accepts to store ResponseElseIf objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}