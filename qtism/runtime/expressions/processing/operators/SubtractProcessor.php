<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\Subtract;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The SubtractProcessor class aims at processing Subtract expressions.
 * 
 * From IMS QTI:
 * 
 * The subtract operator takes 2 sub-expressions which all have single cardinality 
 * and numerical base-types. The result is a single float or, if both sub-expressions 
 * are of integer type, a single integer that corresponds to the first value minus 
 * the second. If either of the sub-expressions is NULL then the operator results in 
 * NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SubtractProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Subtract) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The SubtractProcessor class only processes Subtract QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Subtract operator.
	 * 
	 * @return float|integer|null A single float or if both sub-expressions are integers, a single integer or NULL if either of the sub-expressions is NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Subtract operator only accepts operands with a single cardinality";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		if ($operands->exclusivelyNumeric() === false) {
			$msg = "The Subtract operator only accepts operands with a baseType of integer or float";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$operand1 = $operands[0];
		$operand2 = $operands[1];
		
	 	return $operand1 - $operand2;
	}
}