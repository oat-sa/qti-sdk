<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\operators\DurationLT;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The DurationLTProcessor class aims at processing DurationLT operators.
 * 
 * From IMS QTI:
 * 
 * The durationLT operator takes two sub-expressions which must both have single 
 * cardinality and base-type duration. The result is a single boolean with a value 
 * of true if the first duration is shorter than the second and false if it is 
 * longer than (or equal) to the second. If either sub-expression is NULL then 
 * the operator results in NULL.
 * 
 * There is no 'durationLTE' or 'durationGT' because equality of duration is 
 * meaningless given the variable precision allowed by duration. Given that 
 * duration values are obtained by truncation rather than rounding it makes 
 * sense to test only less-than or greater-than-equal inequalities only. 
 * For example, if we want to determine if a candidate took less than 10 
 * seconds to complete a task in a system that reports durations to a 
 * resolution of epsilon seconds (epsilon<1) then a value equal to 10 would 
 * cover all durations in the range [10,10+epsilon).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DurationLTProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof DurationLT) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The DurationLTProcessor class only processes DurationLT QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the DurationLT operator.
	 * 
	 * @return boolean|null A boolean value of true if the first duration is shorter than the second or NULL if either sub-expression is NULL.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The DurationLT operator only accepts operands with a single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyDuration() === false) {
			$msg = "The DurationLT operator only accepts operands with a duration baseType.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		return $operands[0]->shorterThan($operands[1]);
	}
}