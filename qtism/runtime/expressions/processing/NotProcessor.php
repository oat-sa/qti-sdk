<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\Not;

/**
 * The NotProcessor class aims at processing Not QTI DataModel expressions.
 * 
 * From IMS QTI:
 * 
 * The not operator takes a single sub-expression with a base-type of boolean and single 
 * cardinality. The result is a single boolean with a value obtained by the logical 
 * negation of the sub-expression's value. If the sub-expression is NULL then the not 
 * operator also results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NotProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Not) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The NotProcessor class only processes Not QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Returns the logical negation of the sub-expressions.
	 * 
	 * @return boolean
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull()) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Not Expression only accept operands with single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyBoolean() === false) {
			$msg = "The Not Expression only accept operands with boolean baseType.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$operand = $operands[0];
		return !$operand;
	}
}