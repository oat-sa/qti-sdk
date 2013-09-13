<?php

namespace qtism\runtime\common;

use qtism\common\collections\AbstractCollection;
use qtism\runtime\common\Variable;
use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing runtime Variable objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class VariableCollection extends AbstractCollection {

	/**
	 * Check if $value is a valid Variable object.
	 * 
	 * @throws InvalidArgumentException If $value is not a Variable object.
	 */
	protected function checkType($value) {
		if (!$value instanceof Variable) {
			$msg = "The VariableCollection class only accept Variable objects, '${value}' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}