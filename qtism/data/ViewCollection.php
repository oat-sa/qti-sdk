<?php

namespace qtism\data;

use qtism\common\collections\IntegerCollection;
use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing Views (View enumartion values).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ViewCollection extends IntegerCollection {

	/**
	 * Check if $value is a valid View enumeration value.
	 * 
	 * @throws InvalidArgumentException If $value is not a valid View enumeration value.
	 */
	protected function checkType($value) {
		if (!in_array($value, View::asArray())) {
			$msg = "The ViewsCollection class only accept View enumeration values, '${value}' given.";
			throw new InvalidArgumentException($msg); 
		}
	}
}