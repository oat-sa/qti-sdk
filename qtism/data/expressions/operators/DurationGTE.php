<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The durationGTE operator takes two sub-expressions which must both have single 
 * cardinality and base-type duration. The result is a single boolean with a value
 * of true if the first duration is longer (or equal, within the limits imposed by
 * truncation as described above) than the second and false if it is shorter than
 * the second. If either sub-expression is NULL then the operator results in NULL.
 * 
 * See durationLT for more information about testing the equality of durations.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DurationGTE extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::DURATION));
	}
	
	public function getQTIClassName() {
		return 'durationGTE';
	}
}