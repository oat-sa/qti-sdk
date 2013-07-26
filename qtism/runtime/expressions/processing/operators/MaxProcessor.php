<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\Container;
use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\Max;
use \InvalidArgumentException;

/**
 * The MaxProcessor class aims at processing Max QTI Data Model Expression 
 * objects.
 * 
 * From IMS QTI:
 * 
 * The max operator takes 1 or more sub-expressions which all have numerical base-types 
 * and may have single, multiple or ordered cardinality. The result is a single float, 
 * or, if all sub-expressions are of integer type, a single integer, equal in value to 
 * the greatest of the argument values, i.e. the result is the argument closest to 
 * positive infinity. If the arguments have the same value, the result is that same 
 * value. If any of the sub-expressions is NULL, the result is NULL. If any of the 
 * sub-expressions is not a numerical value, then the result is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MaxProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Max) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The MaxProcessor class only accepts Max QTI Data Model Expression objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the current expression.
	 * 
	 * @return float|integer|null The greatest of the operand values or NULL if any of the operand values is NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->anythingButRecord() === false) {
			$msg = "The Max operator only accept values with a cardinality of single, multiple or ordered.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyNumeric() === false) {
			// As per QTI 2.1 spec, If any of the sub-expressions is not a numerical value, then the result is NULL.
			return null;
		}
		
		// As per QTI 2.1 spec,
		// The result is a single float, or, if all sub-expressions are of 
		// integer type, a single integer.
		$integerCount = 0;
		$valueCount = 0;
		$max = -PHP_INT_MAX;
		foreach ($operands as $operand) {
			if (!$operand instanceof Container) {
				$baseType = (gettype($operand) === 'double') ? BaseType::FLOAT : BaseType::INTEGER;
				$value = new MultipleContainer($baseType, array($operand));
			}
			else {
				$value = $operand;
			}
			
			foreach ($value as $v) {
				$valueCount++;
				$integerCount += (gettype($v) === 'integer') ? 1 : 0;
				
				if ($v > $max) {
					$max = $v;
				}	
			}
		}
		
		return ($integerCount === $valueCount) ? intval($max) : floatval($max);
	}
}