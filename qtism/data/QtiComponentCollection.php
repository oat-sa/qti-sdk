<?php

namespace qtism\data;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * A collection that aims at storing QtiComponent objects. The QtiComponentCollection
 * class must be used as a bag. Thus, no specific key must be set when setting a value
 * in the collection. If a specific key is provided, a RuntimeException will be thrown.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class QtiComponentCollection extends AbstractCollection {

	/**
	 * Check if $value is a QtiComponent object.
	 * 
	 * @throws InvalidArgumentException If $value is not a QtiComponent object.
	 */
	protected function checkType($value) {
		if (!$value instanceof QtiComponent) {
			$msg = "QtiComponentCollection class only accept QtiComponent objects, '" . get_class($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function offsetSet($offset, $value) {
		if (empty($offset)) {
			parent::offsetSet($offset, $value);
		}
		else {
			$msg = "QtiComponentCollection must be used as a bag (specific key '${offset}' given).";
			throw new RuntimeException($msg);
		}
	}
	
	public function offsetUnset($offset) {
		if (empty($offset)) {
			parent::offsetUnset($offset);
		}
		else {
			$msg = "QtiComponentCollection must be used as a bag (specific key '${offset}' given).";
			throw new RuntimeException($msg);
		}
	}
}