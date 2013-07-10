<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\Expression;
use qtism\data\expressions\RandomFloat;
use \InvalidArgumentException;

/**
 * The RandomFloatProcessor class aims at processing RandomFloat QTI Data Model Expression objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RandomFloatProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof RandomFloat) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The RandomFloatProcessor class can only process RandomFloat Expression objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the RandomFloat expression.
	 * 
	 * * Throws an ExpressionProcessingException if 'min' is greater than 'max'.
	 * 
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$expr = $this->getExpression();
		$min = $expr->getMin();
		$max = $expr->getMax();
		
		$state = $this->getState();
		
		$min = (is_float($min)) ? $min : $state[Utils::sanitizeVariableRef($min)];
		$max = (is_float($max)) ? $max : $state[Utils::sanitizeVariableRef($max)];

		if (is_float($min) && is_float($max)) {
			
			if ($min <= $max) {
				return ($min + lcg_value() * (abs($max - $min)));
			}
			else {
				$msg = "'min':'${min}' is greater than 'max':'${max}'.";
				throw new ExpressionProcessingException($msg, $this);
			}
		}
		else {
			$msg = "At least one of the following values is not a float: 'min', 'max'.";
			throw new ExpressionProcessingException($msg, $this);
		}
	}
}