<?php

namespace qtism\data;

use InvalidArgumentException as InvalidArgumentException;
use qtism\common\collections\AbstractCollection;

/**
 * A collection that aims at storing TestFeedback objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestFeedbackCollection extends AbstractCollection {

	/**
	 * Check if $value is a TestFeedbackn object.
	 * 
	 * @throws InvalidArgumentException If $value is not a TestFeedback object.
	 */
	protected function checkType($value) {
		if (!$value instanceof TestFeedback) {
			$msg = "TestFeedbackCollection class only accept TestFeedback objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}