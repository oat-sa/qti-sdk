<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\Expression;
use qtism\data\expressions\NullValue;

/**
 * The NullProcessor class aims at processing NullValue QTI DataModel expressions.
 * 
 * From IMS QTI:
 * 
 * null is a simple expression that returns the NULL value - the null value is 
 * treated as if it is of any desired baseType.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NullProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof NullValue) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The NullProcessor class only processes NullValue QTI Data Model objects.";
			throw new InvalidArgumentException();
		}
	}
	
	/**
	 * Returns NULL.
	 * 
	 * @return null The null value.
	 */
	public function process() {
		return null;
	}
}