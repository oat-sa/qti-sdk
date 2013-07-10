<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\DefaultVal;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The DefaultProcessor class aims at processing Default QTI Data Model Expressions.
 * 
 * From IMS QTI:
 * 
 * This expression looks up the declaration of an itemVariable and returns the associated
 * defaultValue or NULL if no default value was declared. When used in outcomes processing
 * item identifier prefixing (see variable) may be used to obtain the default value from an 
 * individual item.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DefaultProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof DefaultVal) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The DefaultProcessor class only accepts a Default Expression to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Returns the defaultValue of the current Expression to be processed. If no Variable
	 * with the given identifier is found, null is returned. If the Variable has no defaultValue,
	 * null is returned.
	 * 
	 * @return mixed A QTI Runtime compliant value.
	 */
	public function process() {
		$expr = $this->getExpression();
		$state = $this->getState();
		
		$var = $state->getVariable($expr->getIdentifier());
		return ($var === null) ? null : $var->getDefaultValue();
	}
}