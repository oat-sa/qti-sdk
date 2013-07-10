<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of BranchRule objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BranchRuleCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of BranchRule.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of BranchRule.
	 */
	protected function checkType($value) {
		if (!$value instanceof BranchRule) {
			$msg = "BranchRuleCollection only accepts to store BranchRule objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}