<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\StringMatch;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The StringMatchProcessor class aims at processing StringMatch operators.
 * 
 * Please note that this implementation does not take care of the deprecated
 * attribute 'substring'.
 * 
 * From IMS QTI:
 * 
 * The stringMatch operator takes two sub-expressions which must have single and 
 * a base-type of string. The result is a single boolean with a value of true if 
 * the two strings match according to the comparison rules defined by the attributes 
 * below and false if they don't. If either sub-expression is NULL then the operator 
 * results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StringMatchProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof StringMatch) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The StringMatchProcessor class only processes StringMatch QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the StringMatch operator.
	 * 
	 * @return boolean Whether the two string match according to the comparison rules of the operator's attributes or NULL if either of the sub-expressions is NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The StringMatch operator only accepts operands with a single cardinality.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyString() === false) {
			$msg = "The StringMatch operator only accepts operands with a string baseType.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		$expression = $this->getExpression();
		
		// choose the correct comparison function according comparison rules
		// of the operator.
		// Please note that strcmp and strcasecmp are binary-safe *\0/* Hourray! *\0/* 
		$func = ($expression->isCaseSensitive() === true) ? 'strcmp' : 'strcasecmp';
		return $func($operands[0], $operands[1]) === 0;
	}
}