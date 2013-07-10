<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of OutcomeRule objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeRuleCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of OutcomeRule.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of OutcomeRule.
	 */
	protected function checkType($value) {
		if (!$value instanceof OutcomeRule) {
			$msg = "OutcomeRuleCollection only accepts to store OutcomeRule objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}