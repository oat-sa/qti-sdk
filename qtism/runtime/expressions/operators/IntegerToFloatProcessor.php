<?php

namespace qtism\runtime\expressions\operators;

use qtism\data\expressions\operators\IntegerToFloat;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The IntegerToFloatProcessor class aims at processing IntegerToFloat operators.
 * 
 * From IMS QTI:
 * 
 * The integer to float conversion operator takes a single sub-expression which must 
 * have single cardinality and base-type integer. The result is a value of base type 
 * float with the same numeric value. If the sub-expression is NULL then the operator 
 * results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IntegerToFloatProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof IntegerToFloat) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The IntegerToFloatProcessor class only processes IntegerToFloat QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the IntegerToFloat operator.
	 * 
	 * @return float|null A float value with the same numeric value as the sub-expression or NULL if the sub-expression is considered to be NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The IntegerToFloat operator only accepts operands with a single cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		if ($operands->exclusivelyInteger() === false) {
			$msg = "The IntegerToFloat operator only accepts operands with baseType integer.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$operand = $operands[0];
		return floatval($operand);
	}
}