<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\Expression;
use qtism\data\expressions\NullValue;

/**
 * The NullProcessor class aims at processing NullValue QTI DataModel expressions.
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
			$msg = "The NullProcessor class only processos NullValue QTI Data Model objects.";
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