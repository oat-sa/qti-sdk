<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\ContainerSize;
use \InvalidArgumentException;

/**
 * The ContainerSizeProcessor class aims at processing ContainerSize QTI Data Model Expression objects.
 * 
 * From IMS QTI:
 * 
 * The containerSize operator takes a sub-expression with any base-type and either multiple or ordered cardinality.
 * The result is an integer giving the number of values in the sub-expression, in other words, the size of the container.
 * If the sub-expression is NULL the result is 0. This operator can be used for determining how many choices were selected
 * in a multiple-response choiceInteraction, for example.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ContainerSizeProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof ContainerSize) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The ContainerSizeProcessor class only accepts ContainerSize QTI Data Model Expression objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the current expression.
	 * 
	 * @return integer|null The size of the container or null if it contains NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return 0;
		}
		
		if ($operands->exclusivelyMultipleOrOrdered() === false) {
			$msg = "The ContainerSize operator only accepts operands with a multiple or ordered cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		return count($operands[0]);
	}
}