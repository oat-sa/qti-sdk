<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of ResponseRule objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseRuleCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of ResponseRule.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of ResponseRule.
	 */
	protected function checkType($value) {
		if (!$value instanceof ResponseRule) {
			$msg = "ResponseRuleCollection only accepts to store ResponseRule objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}