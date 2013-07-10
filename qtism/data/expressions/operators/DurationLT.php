<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The durationLT operator takes two sub-expressions which must both have single
 * cardinality and base-type duration. The result is a single boolean with a value
 * of true if the first duration is shorter than the second and false if it is longer
 * than (or equal) to the second. If either sub-expression is NULL then the operator
 * results in NULL.
 * 
 * There is no 'durationLTE' or 'durationGT' because equality of duration is 
 * meaningless given the variable precision allowed by duration. Given that 
 * duration values are obtained by truncation rather than rounding it makes 
 * sense to test only less-than or greater-than-equal inequalities only.
 * For example, if we want to determine if a candidate took less than 10 
 * seconds to complete a task in a system that reports durations to a 
 * resolution of epsilon seconds (epsilon<1) then a value equal to 10 
 * would cover all durations in the range [10,10+epsilon).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DurationLT extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::DURATION));
	}
	
	public function getQTIClassName() {
		return 'durationLT';
	}
}