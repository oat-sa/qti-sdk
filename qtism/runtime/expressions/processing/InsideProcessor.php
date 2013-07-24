<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\operators\Inside;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The InsideProcessor class aims at processing Inside operators.
 * 
 * From IMS QTI:
 * 
 * The inside operator takes a single sub-expression which must have a baseType of 
 * point. The result is a single boolean with a value of true if the given point is 
 * inside the area defined by shape and coords. If the sub-expression is a container 
 * the result is true if any of the points are inside the area. If either 
 * sub-expression is NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class InsideProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Inside) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The InsideProcessor class only processes Inside QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Inside operator.
	 * 
	 * @return boolean|null Whether the given point is inside the area defined by shape and coords or NULL if the sub-expression is NULL.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The Inside operator only accepts operands with a single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyPoint() === false) {
			$msg = "The Inside operator only accepts operands with a baseType of point.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$operand = $operands[0];
		$coords = $this->getExpression()->getCoords();
		
		return $coords->inside($operand);
	}
}