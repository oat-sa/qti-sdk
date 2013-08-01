<?php

namespace qtism\data;

use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing AssessmentItemRef objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentItemRefCollection extends SectionPartCollection {

	/**
	 * Check if $value is an AssessmentItemRef object.
	 * 
	 * @throws InvalidArgumentException If $value is not a AssessmentItemRef object.
	 */
	protected function checkType($value) {
		parent::checkType($value);
		if (!$value instanceof AssessmentItemRef) {
			$msg = "AssessmentItemRefCollection class only accept AssessmentItemRef objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}