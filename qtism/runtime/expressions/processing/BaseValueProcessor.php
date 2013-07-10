<?php

namespace qtism\runtime\expressions\processing;

/**
 * The BaseValueProcessor class aims at processing BaseValue expressions.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BaseValueProcessor extends ExpressionProcessor {
	
	/**
	 * Process the BaseValue.
	 */
	public function process() {
		return $this->getExpression()->getValue();
	}
}