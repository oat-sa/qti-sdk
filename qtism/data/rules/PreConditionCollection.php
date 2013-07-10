<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of PreCondition objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PreConditionCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of PreCondition.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of PreCondition.
	 */
	protected function checkType($value) {
		if (!$value instanceof PreCondition) {
			$msg = "PreConditionCollection only accepts to store PreCondition objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}