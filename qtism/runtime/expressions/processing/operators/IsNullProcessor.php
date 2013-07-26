<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\IsNull;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The IsNullProcessor class aims at processing IsNull QTI Data Model Expression objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IsNullProcessor extends OperatorProcessor {
	
	/**
	 * Set the Expression object to be processed.
	 * 
	 * @param Expression An IsNull object.
	 * @throws InvalidArgumentException If the $expression is not an IsNull QTI Data Model Expression object.
	 */
	public function setExpression(Expression $expression) {
		if ($expression instanceof IsNull) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The IsNullProcessor class only accept IsNull Operator objects to be processed.";
			throw new InvalidArgumentException($expression);
		}
	}
	
	/**
	 * Process the IsNullExpression object from the QTI Data Model.
	 * 
	 * @throws OperatorProcessingException If something goes wrong.
	 */
	public function process() {
		$operands = $this->getOperands();
		$expression = $this->getExpression();
		
		return $operands->containsNull();
	}
	
}