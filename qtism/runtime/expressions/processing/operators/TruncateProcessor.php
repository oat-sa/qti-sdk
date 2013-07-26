<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\Truncate;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The TruncateProcessor class aims at processing Truncate expressions.
 * 
 * From IMS QTI:
 * 
 * The truncate operator takes a single sub-expression which must have single
 * cardinality and a numerical base-type. The result is a value of base-type 
 * integer formed by truncating the value of the sub-expression towards zero.
 * For example, the value 6.8 becomes 6 and the value -6.8 becomes -6. If 
 * the sub-expression is NULL then the operator results in NULL. If the 
 * sub-expression is NaN, then the result is NULL. If the sub-expression is 
 * INF, then the result is INF. If the sub-expression is -INF, then the 
 * result is -INF.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TruncateProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Truncate) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The TruncateProcessor class only processes Truncate QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Truncate operator.
	 * 
	 * @return integer|null The truncated value or NULL if the sub-expression is NaN or if the sub-expression is NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Truncate operator only accepts operands with a single cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		if ($operands->exclusivelyNumeric() === false) {
			$msg = "The Truncate operator only accepts operands with an integer or float baseType.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$operand = $operands[0];
		
		if (is_nan($operand)) {
			return null;
		}
		
		return intval($operand);
	}
}