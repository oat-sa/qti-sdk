<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\Lte;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The LteProcessor class aims at processing Lte operators.
 * 
 * From IMS QTI:
 * 
 * The lte operator takes two sub-expressions which must both have single cardinality 
 * and have a numerical base-type. The result is a single boolean with a value of 
 * true if the first expression is numerically less than or equal to the second and 
 * false if it is greater than the second. If either sub-expression is NULL then the 
 * operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class LteProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Lte) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The LteProcessor class only processes Lte QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Lte operator.
	 * 
	 * @return boolean|null Whether the first sub-expression is numerically less than or equal to the second or NULL if either sub-expression is NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Lte operator only accepts operands with a single cardinality.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyNumeric() === false) {
			$msg = "The Lte operator only accepts operands with a float or integer baseType.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		return $operands[0] <= $operands[1];
	}
}