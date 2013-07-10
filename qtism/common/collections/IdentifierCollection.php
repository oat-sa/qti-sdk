<?php

namespace qtism\common\collections;

use InvalidArgumentException as InvalidArgumentException;
use qtism\common\utils\Format as Format;

/**
 * A collection that aims at storing string values.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IdentifierCollection extends AbstractCollection {

	/**
	 * Check if $value is a valid QTI Identifier.
	 * 
	 * @throws InvalidArgumentException If $value is not a valid QTI Identifier.
	 */
	protected function checkType($value) {
		if (!is_string($value)) {
			$msg = "IdentifierCollection class only accept string values, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
		else if (!Format::isIdentifier($value)) {
			$msg = "IdentifierCollection class only accept valid QTI Identifiers, '${value}' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}