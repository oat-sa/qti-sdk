<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\Divide;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The DivideProcessor class aims at processing Divide operators.
 * 
 * From IMS QTI:
 * 
 * The divide operator takes 2 sub-expressions which both have single cardinality and
 * numerical base-types. The result is a single float that corresponds to the first
 * expression divided by the second expression. If either of the sub-expressions is
 * NULL then the operator results in NULL.
 * 
 * Item authors should make every effort to ensure that the value of the second
 * expression is never 0, however, if it is zero or the resulting value is outside
 * the value set defined by float (not including positive and negative infinity) then
 * the operator should result in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DivideProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Divide) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The DivideProcessor class only processes Divide QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Divide operator.
	 * 
	 * @return float|null A float value that corresponds to the first expression divided by the second or NULL if either of the sub-expressions is NULL or the result is outside the value set defined by float.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Divide operator only accepts operands with a single cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		if ($operands->exclusivelyNumeric() === false) {
			$msg = "The Divide operator only accepts operands with a baseType of integer or float.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$operand1 = $operands[0];
		$operand2 = $operands[1];
		
		if ($operand2 == 0) {
			return null;
		}
		
		$divide = floatval($operand1 / $operand2);
		
		return is_nan($divide) ? null : $divide;
	}
}