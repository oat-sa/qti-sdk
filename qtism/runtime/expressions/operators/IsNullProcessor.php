<?php

namespace qtism\runtime\expressions\operators;

use qtism\data\expressions\operators\IsNull;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The IsNullProcessor class aims at processing IsNull QTI Data Model Expression objects.
 * 
 * From IMS QTI:
 * 
 * The isNull operator takes a sub-expression with any base-type and cardinality. 
 * The result is a single boolean with a value of true if the sub-expression is NULL 
 * and false otherwise. Note that empty containers and empty strings are both 
 * treated as NULL.
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
	 * @return boolean Whether the sub-expression is considered to be NULL.
	 * @throws OperatorProcessingException If something goes wrong.
	 */
	public function process() {
		$operands = $this->getOperands();
		$expression = $this->getExpression();
		
		return $operands->containsNull();
	}
	
}