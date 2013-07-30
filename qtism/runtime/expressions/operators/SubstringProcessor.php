<?php

namespace qtism\runtime\expressions\operators;

use qtism\data\expressions\operators\Substring;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The SubstringProcessor class aims at processing Substring operator.
 * 
 * From IMS QTI:
 * 
 * The substring operator takes two sub-expressions which must both have an
 * effective base-type of string and single cardinality. The result is a 
 * single boolean with a value of true if the first expression is a substring 
 * of the second expression and false if it isn't. If either sub-expression is 
 * NULL then the result of the operator is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SubstringProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Substring) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The SubstringProcessor class only processes Substring QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Substring operator.
	 * 
	 * @return boolean|null Whether the first sub-expression is a substring of the second sub-expression or NULL if either sub-expression is NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Substring operator only accepts operands with a single cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		if ($operands->exclusivelyString() === false) {
			$msg = "The Substring operator only accepts operands with a string baseType.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$operand1 = $operands[0];
		$operand2 = $operands[1];
		
		$call = ($this->getExpression()->isCaseSensitive() === true) ? 'mb_strpos' : 'mb_stripos';
		return $call($operand2, $operand1, 0, 'UTF-8') !== false;
	}
}