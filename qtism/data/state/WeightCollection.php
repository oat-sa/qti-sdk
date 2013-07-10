<?php

namespace qtism\data\state;

use qtism\data\QtiIdentifiableCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of Weight objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class WeightCollection extends QtiIdentifiableCollection {
	
	/**
	 * Check if a given $value is an instance of Weight.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of Weight.
	 */
	protected function checkType($value) {
		if (!$value instanceof Weight) {
			$msg = "WeightCollection only accepts to store Weight objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}