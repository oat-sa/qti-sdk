<?php

namespace qtism\runtime\tests;

use qtism\common\collections\AbstractCollection;
use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing Route objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SelectableRouteCollection extends AbstractCollection {

	/**
	 * Check if $value is a Route object
	 * 
	 * @throws InvalidArgumentException If $value is not a Route object.
	 */
	protected function checkType($value) {
		if (!$value instanceof SelectableRoute) {
			$msg = "SelectableRouteCollection class only accept SelectableRoute objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}