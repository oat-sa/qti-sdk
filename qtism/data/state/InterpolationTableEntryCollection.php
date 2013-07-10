<?php

namespace qtism\data\state;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of InterpolationTableEntry objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class InterpolationTableEntryCollection extends AbstractCollection {
	
	/**
	 * Check if a given $value is an instance of InterpolationTableEntry.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of InterpolationTableEntry.
	 */
	protected function checkType($value) {
		if (!$value instanceof InterpolationTableEntry) {
			$msg = "InterpolationTableEntryCollection only accepts to store InterpolationTableEntry objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}