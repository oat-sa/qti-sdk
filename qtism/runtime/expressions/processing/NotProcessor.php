<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\Not;

/**
 * The NotProcessor class aims at processing Not QTI DataModel expressions.
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
			$msg = "The NotProcessor class only processos Not QTI Data Model objects.";
			throw new InvalidArgumentException();
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