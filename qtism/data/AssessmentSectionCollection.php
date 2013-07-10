<?php

namespace qtism\data;

use InvalidArgumentException as InvalidArgumentException;
use qtism\common\collections\AbstractCollection;

/**
 * A collection that aims at storing AssessmentSection objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentSectionCollection extends AbstractCollection {

	/**
	 * Check if $value is an AssessmentSection object.
	 * 
	 * @throws InvalidArgumentException If $value is not a AssessmentSection object.
	 */
	protected function checkType($value) {
		if (!$value instanceof AssessmentSection) {
			$msg = "AssessmentSectionCollection class only accept AssessmentSection objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}