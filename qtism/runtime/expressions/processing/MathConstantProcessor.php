<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\MathEnumeration;
use qtism\data\expressions\Expression;
use qtism\data\expressions\MathConstant;
use \InvalidArgumentException;

/**
 * The MathConstant processor aims at processing QTI Data Model MathConstant expressions.
 * 
 * From IMS QTI:
 * 
 * The result is a mathematical constant returned as a single float, e.g. π and e.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MathConstantProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof MathConstant) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The MathConstantProcessor class only processes MathConstant QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the MathConstant Expression. 
	 * 
	 * @return float A float value (e or pi).
	 */
	public function process() {
		$expr = $this->getExpression();
		if ($expr->getName() === MathEnumeration::E) {
			return M_E;
		}
		else {
			return M_PI;
		}
	}
}