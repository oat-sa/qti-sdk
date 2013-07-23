<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\BaseValue;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The BaseValueProcessor class aims at processing BaseValue expressions.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BaseValueProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof BaseValue) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The BaseValueProcessor class only processes BaseValue QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the BaseValue.
	 */
	public function process() {
		return $this->getExpression()->getValue();
	}
}