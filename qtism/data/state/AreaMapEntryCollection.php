<?php

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of AreaMapEntry objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AreaMapEntryCollection extends QtiComponentCollection {
	
	/**
	 * Check if a given $value is an instance of AreaMapEntry.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of AreaMapEntry.
	 */
	protected function checkType($value) {
		parent::checkType($value);
		
		if (!$value instanceof AreaMapEntry) {
			$msg = "AreaMapEntryCollection only accepts to store AreaMapEntry objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}