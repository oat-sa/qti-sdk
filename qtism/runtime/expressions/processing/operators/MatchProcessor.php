<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\runtime\common\Container;
use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\Match;
use \InvalidArgumentException;

/**
 * The MatchProcessor class aims at processing Match QTI Data Model Expression objects.
 * 
 * From IMS QTI:
 * 
 * The match operator takes two sub-expressions which must both have the same 
 * base-type and cardinality. The result is a single boolean with a value of 
 * true if the two expressions represent the same value and false if they do not.
 * If either sub-expression is NULL then the operator results in NULL.
 * 
 * The match operator must not be confused with broader notions of equality such as numerical equality.
 * To avoid confusion, the match operator should not be used to compare subexpressions with base-types 
 * of float and must not be used on sub-expressions with a base-type of duration.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MatchProcessor extends OperatorProcessor {
	
	/**
	 * Set the Expression object to be processed.
	 * 
	 * @param Expression $expression An Expression object to be processed.
	 * @throws InvalidArgumentException If $expression is not a Match QTI Data Model Expression object.
	 */
	public function setExpression(Expression $expression) {
		if ($expression instanceof Match) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The MatchProcessor only accepts Match QTI Data Model Expression objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Match Expression object.
	 * 
	 * @return boolean
	 */
	public function process() {
		$operands = $this->getOperands();
		$expression = $this->getExpression();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->sameCardinality() === false) {
			$msg = "The Match Expression only accepts operands with the same cardinality.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		if ($operands->sameBaseType() === false) {
			$msg = "The Match Expression only accepts operands with the same baseType.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		$firstOperand = $operands[0];
		$secondOperand = $operands[1];
		
		if ($operands[0] instanceof Container) {
			// 2 containers to compare.
			return $operands[0]->equals($operands[1]);
		}
		else {
			return $operands[0] === $operands[1];
		}
	}
}