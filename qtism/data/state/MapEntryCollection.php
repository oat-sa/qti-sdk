<?php

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of MapEntry objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MapEntryCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of MapEntry.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of MapEntry.
	 */
	protected function checkType($value) {
		if (!$value instanceof MapEntry) {
			$msg = "MapEntryCollection only accepts to store MapEntry objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}