<?php

namespace qtism\data;

use qtism\runtime\common\OutcomeVariable;
use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing runtime OutcomeVariable objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeVariableCollection extends VariableCollection {

	/**
	 * Check if $value is a valid OutcomeVariable object.
	 * 
	 * @throws InvalidArgumentException If $value is not a OutcomeVariable object.
	 */
	protected function checkType($value) {
		if (!$value instanceof Variable) {
			$msg = "The OutcomeVariableCollection class only accept OutcomeVariable objects, '${value}' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}