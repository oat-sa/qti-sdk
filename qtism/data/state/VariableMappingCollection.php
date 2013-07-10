<?php

namespace qtism\data\state;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of VariableMapping objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class VariableMappingCollection extends AbstractCollection {
	
	/**
	 * Check if a given $value is an instance of VariableMapping.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of VariableMapping.
	 */
	protected function checkType($value) {
		if (!$value instanceof VariableMapping) {
			$msg = "VariableMappingCollection only accepts to store VariableMapping objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}