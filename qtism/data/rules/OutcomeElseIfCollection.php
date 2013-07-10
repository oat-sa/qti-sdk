<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of OutcomeElseIf objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeElseIfCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of OutcomeElseIf.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of OutcomeElseIf.
	 */
	protected function checkType($value) {
		if (!$value instanceof OutcomeElseIf) {
			$msg = "OutcomeElseIfCollection only accepts to store OutcomeElseIf objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}