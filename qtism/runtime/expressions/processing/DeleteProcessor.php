<?php

namespace qtism\runtime\expressions\processing;


use qtism\common\Comparable;

use qtism\runtime\common\OrderedContainer;

use qtism\runtime\common\MultipleContainer;

use qtism\common\enums\Cardinality;

use qtism\runtime\common\Utils as RuntimeUtils;
use qtism\data\expressions\operators\Delete;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The DeleteProcessor class aims at processing Delete operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DeleteProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Delete) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The DeleteProcessor class only processes Delete QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Delete operator.
	 * 
	 * @return A new container derived from the second sub-expression with all instances of the first sub-expression removed, or NULL if either sub-expression is considered to be NULL.
	 * @throws ExpressionProcessingException 
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->sameBaseType() === false) {
			$msg = "The Delete operator only accepts operands with the same baseType.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$operand1 = $operands[0];
		if (RuntimeUtils::inferCardinality($operand1) !== Cardinality::SINGLE) {
			$msg = "The first operand of the Delete operator must have the single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$operand2 = $operands[1];
		$cardinality = RuntimeUtils::inferCardinality($operand2);
		if ($cardinality !== Cardinality::MULTIPLE && $cardinality !== Cardinality::ORDERED) {
			$msg = "The second operand of the Delete operator must have a cardinality or multiple or ordered.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$returnedBaseType = RuntimeUtils::inferBaseType($operand1);
		$returnValue = ($cardinality === Cardinality::MULTIPLE) ? new MultipleContainer($returnedBaseType) : new OrderedContainer($returnedBaseType);
		
		foreach ($operand2 as $value) {
			if ($value === $operand1 || ($operand1 instanceof Comparable && $operand1->equals($value) === true)) {
				// This is the same value, it will not be included in the returned value.
				continue;
			}
			else {
				$returnValue[] = $value;
			}
		}
		
		return $returnValue;
	}
}