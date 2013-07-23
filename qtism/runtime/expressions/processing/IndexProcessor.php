<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\operators\Index;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The IndexProcessor class aims at processing Index operators.
 * 
 * From IMS QTI:
 * 
 * The index operator takes a sub-expression with an ordered container value and any
 * base-type. The result is the nth value of the container. The result has the same
 * base-type as the sub-expression but single cardinality. The first value of a container
 * has index 1, the second 2 and so on. n must be a positive integer. If n exceeds the
 * number of values in the container (or the sub-expression is NULL) then the result
 * of the index operator is NULL. If n is an identifier, it is the value of n at
 * runtime that is used.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IndexProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Index) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The IndexProcessor class only processes Index QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Index operator.
	 * 
	 * @return mixed|null A QTIRuntime compliant scalar value. NULL is returned if expression->n exceeds the number of values in the container or the sub-expression is NULL.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull()) {
			return null;
		}
		
		if ($operands->exclusivelyOrdered() === false) {
			$msg = "The Index operator only accepts values with a cardinality of ordered.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$n = $this->getExpression()->getN();
		if (is_string($n)) {
			// The value of $n comes from the state.
			$state = $this->getState();
			if (($index = $state[Utils::sanitizeVariableRef($n)]) !== null) {
				if (gettype($index) === 'integer') {
					$n = $index;
				}
				else {
					$msg = "The value '${index}' is not an integer. Ordered containers can be only accessed by integers.";
					throw new ExpressionProcessingException($msg, $this);
				}
			}
			else {
				$msg = "Unknown variable reference '${n}'.";
				throw new ExpressionProcessingException($msg, $this);
			}
		}
		
		if ($n < 1) {
			$msg = "The value of 'n' must be a non-zero postive integer, '${n}' given.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$n = $n - 1; // QTI indexes begin at 1...
		if ($n > count($operands[0]) - 1) {
			// As per specs, if n exceeds the number of values in the container,
			// the result of the index operator is NULL.
			return null;
		}
		
		return $operands[0][$n];
	}
}