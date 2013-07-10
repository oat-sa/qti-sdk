<?php

namespace qtism\data;

use InvalidArgumentException as InvalidArgumentException;
use qtism\common\collections\AbstractCollection;

/**
 * A collection that aims at storing Stylesheet objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StylesheetCollection extends AbstractCollection {

	/**
	 * Check if $value is a Stylesheet object.
	 * 
	 * @throws InvalidArgumentException If $value is not a Stylesheet object.
	 */
	protected function checkType($value) {
		if (!$value instanceof Stylesheet) {
			$msg = "StylesheetCollection class only accept Stylesheet objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}