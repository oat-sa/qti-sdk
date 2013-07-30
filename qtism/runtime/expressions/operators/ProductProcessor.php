<?php

namespace qtism\runtime\expressions\operators;

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\data\expressions\operators\Product;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The ProductProcessor class aims at processing Product QTI Data Model Operators.
 * 
 * From IMS QTI:
 * 
 * The product operator takes 1 or more sub-expressions which all have numerical 
 * base-types and may have single, multiple or ordered cardinality. The result is 
 * a single float or, if all sub-expressions are of integer type, a single integer 
 * that corresponds to the product of the numerical values of the sub-expressions. 
 * If any of the sub-expressions are NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ProductProcessor extends OperatorProcessor {
	
	/**
	 * Set the Product Expression object to be processed.
	 * 
	 * @param Expression $expression A Product object.
	 * @throws InvalidArgumentException If $expression is not an instance of Product.
	 */
	public function setExpression(Expression $expression) {
		if ($expression instanceof Product) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The ProductProcessor class only accepts a Product Operator to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Product operator.
	 * 
	 * @throws OperatorProcessingException If invalid operands are given.
	 */
	public function process() {
		
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		else if ($operands->anythingButRecord() === false) {
			$msg = "The Product operator only accepts operands with a single, multiple or ordered cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		else if ($operands->exclusivelyNumeric() === false) {
			$msg = "The Product operator only accepts operands with integer or float baseType.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$returnValue = 1;
		
		foreach ($this->getOperands() as $operand) {
			if (gettype($operand) !== 'object') {
				$returnValue *= $operand;
			}
			else {
				foreach ($operand as $val) {
					$returnValue *= $val;
				}
			}
		}
		
		return $returnValue;
	}
}