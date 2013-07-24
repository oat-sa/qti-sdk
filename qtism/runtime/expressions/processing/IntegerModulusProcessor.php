<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\operators\IntegerModulus;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The IntegerModulusProcessor class aims at processing IntegerModulus operators.
 * 
 * From IMS QTI:
 * 
 * The integer modulus operator takes 2 sub-expressions which both have single 
 * cardinality and base-type integer. The result is the single integer that 
 * corresponds to the remainder when the first expression (x) is divided by 
 * the second expression (y). If z is the result of the corresponding integerDivide 
 * operator then the result is x-z*y. If y is 0, or if either of the sub-expressions 
 * is NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IntegerModulusProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof IntegerModulus) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The IntegerModulusProcessor class only processes IntegerModulus QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the IntegerModulus operator.
	 * 
	 * @return integer|null An integer value that corresponds to the remainder of the Integer Division or NULL if the second expression is 0 or if either of the sub-expressions is NULL.
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The IntegerModulus operator only accepts operands with single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyInteger() === false) {
			$msg = "The IntegerModulus operator only accepts operands with baseType integer.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$operand1 = $operands[0];
		$operand2 = $operands[1];
		
		if ($operand2 == 0) {
			// modulus by zero forbidden.
			return null;
		}
		
		return intval($operand1 % $operand2);
	}
}