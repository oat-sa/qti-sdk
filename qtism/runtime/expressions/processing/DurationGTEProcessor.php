<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\operators\DurationGTE;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The DurationGTEProcessor class aims at processing DurationGTE operators.
 * 
 * From IMS QTI:
 * 
 * The durationGTE operator takes two sub-expressions which must both have 
 * single cardinality and base-type duration. The result is a single boolean with a 
 * value of true if the first duration is longer (or equal, within the limits imposed 
 * by truncation as described above) than the second and false if it is shorter than 
 * the second. If either sub-expression is NULL then the operator results in NULL.
 * 
 * See durationLT for more information about testing the equality of durations.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DurationGTEProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof DurationGTE) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The DurationGTEProcessor class only processes DurationGTE QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the DurationGTE operator.
	 * 
	 * @return boolean|null A boolean with a value of true if the first duration is longer or equal to the second, otherwise false. If either sub-expression is NULL, the result of the operator is NULL.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The DurationGTE operator only accepts operands with a single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyDuration() === false) {
			$msg = "The DurationGTE operator only accepts operands with a duration baseType.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		return $operands[0]->longerThanOrEquals($operands[1]);
	}
}