<?php

namespace qtism\data\state;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of MatchTableEntry objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MatchTableEntryCollection extends AbstractCollection {
	
	/**
	 * Check if a given $value is an instance of MatchTableEntry.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of MatchTableEntry.
	 */
	protected function checkType($value) {
		if (!$value instanceof MatchTableEntry) {
			$msg = "MatchTableEntryCollection only accepts to store MatchTableEntry objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}