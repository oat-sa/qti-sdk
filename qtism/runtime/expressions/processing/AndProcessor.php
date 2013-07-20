<?php

use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\AndOperator;
use \InvalidArgumentException;

/**
 * The AndProcessor class aims at processing AndOperator QTI Data Model Expression objects.
 * 
 * From IMS QTI:
 * 
 * The and operator takes one or more sub-expressions each with a base-type of boolean and single 
 * cardinality. The result is a single boolean which is true if all sub-expressions are true and
 * false if any of them are false. If one or more sub-expressions are NULL and all others are true
 * then the operator also results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AndProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof AndOperator) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The AndProcessor class only accepts AndOperator QTI Data Model Expression objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the current expression.
	 * 
	 * @return boolean True if the expression is true, false otherwise.
	 * @ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The And Expression only accept operands with single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyBoolean() === false) {
			$msg = "The And Expression only accept operands with boolean baseType.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		foreach ($operands as $operand) {
			if ($operand === false) {
				return false;
			}
		}
		
		return true;
	}
}