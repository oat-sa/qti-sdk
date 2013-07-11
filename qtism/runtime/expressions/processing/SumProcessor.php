<?php

namespace qtism\runtime\expressions\processing;

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\data\expressions\operators\Sum;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The SumProcessor class aims at processing Sum QTI Data Model Expressions.
 * 
 * From IMS QTI:
 * 
 * The sum operator takes 1 or more sub-expressions which all have numerical base-types 
 * and may have single, multiple or ordered cardinality. The result is a single float or,
 * if all sub-expressions are of integer type, a single integer that corresponds to the
 * sum of the numerical values of the sub-expressions. If any of the sub-expressions are 
 * NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SumProcessor extends OperatorProcessor {
	
	/**
	 * Set the Sum Expression object to be processed.
	 * 
	 * @param Expression $expression A Sum object.
	 * @throws InvalidArgumentException If $expressions is not an instance of Sum.
	 */
	public function setExpression(Expression $expression) {
		if ($expression instanceof Sum) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The SumProcessor class only accepts a Sum Expression to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Sum.
	 * 
	 * @throws ExpressionProcessingException If invalid operands are given.
	 */
	public function process() {
		
		$operands = $this->getOperands();
		
		if ($operands->containsNull()) {
			return null;
		}
		else if (!$operands->exclusivelyNumeric()) {
			$msg = "The Sum Operator only accepts numeric values.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$returnValue = 0;
		
		foreach ($this->getOperands() as $operand) {
			if (gettype($operand) !== 'object') {
				$returnValue += $operand;
			}
			else {
				foreach ($operand as $val) {
					$returnValue += $val;
				}
			}
		}
		
		return $returnValue;
	}
}