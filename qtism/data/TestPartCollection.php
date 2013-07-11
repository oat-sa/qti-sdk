<?php

namespace qtism\data;

use InvalidArgumentException as InvalidArgumentException;
use qtism\common\collections\AbstractCollection;

/**
 * A collection that aims at storing TestPart objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestPartCollection extends QtiIdentifiableCollection {

	/**
	 * Check if $value is a TestPart object.
	 * 
	 * @throws InvalidArgumentException If $value is not a TestPart object.
	 */
	protected function checkType($value) {
		if (!$value instanceof TestPart) {
			$msg = "TestPartCollection class only accept TestPart objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}