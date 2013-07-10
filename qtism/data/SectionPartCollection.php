<?php

namespace qtism\data;

use InvalidArgumentException as InvalidArgumentException;
use qtism\data\QtiComponentCollection;

/**
 * A collection that aims at storing SectionPart objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SectionPartCollection extends QtiComponentCollection {

	/**
	 * Check if $value is a SectionPart object.
	 * 
	 * @throws InvalidArgumentException If $value is not a SectionPart object.
	 */
	protected function checkType($value) {
		if (!$value instanceof SectionPart) {
			$msg = "SectionPartCollection class only accept SectionPart objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}