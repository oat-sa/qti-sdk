<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\OrOperator;
use \InvalidArgumentException;

/**
 * The OrProcessor class aims at processing OrOperator QTI Data Model Expression objects.
 * 
 * Developer's note: IMS does not explain what happens when one or more sub-expressions are NULL
 * but not all sub-expressions are false. In this implementation, we consider that NULL is returned
 * if one ore more sub-expressions are NULL.
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
class OrProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof OrOperator) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The AndProcessor class only accepts OrOperator QTI Data Model Expression objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the current expression.
	 * 
	 * @return boolean True if the expression is true, false otherwise.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Or Expression only accept operands with single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyBoolean() === false) {
			$msg = "The Or Expression only accept operands with boolean baseType.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		foreach ($operands as $operand) {
			if ($operand === true) {
				return true;
			}
		}
		
		return false;
	}
}