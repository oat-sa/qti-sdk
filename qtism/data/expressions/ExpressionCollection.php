<?php

namespace qtism\data\expressions;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of Expression objects.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExpressionCollection extends QtiComponentCollection {

	/**
	 * Check if a given $value is an instance of Expression.
	 *
	 * @throws InvalidArgumentException If the given $value is not an instance of Expression.
	 */
	protected function checkType($value) {
		parent::checkType($value);
		
		if (!$value instanceof Expression) {
			$msg = "ExpressionCollection only accepts to store Expression objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}