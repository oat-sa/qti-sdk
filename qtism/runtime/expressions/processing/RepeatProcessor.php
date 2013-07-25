<?php

namespace qtism\runtime\expressions\processing;

use qtism\runtime\common\Container;
use qtism\runtime\common\Utils as RuntimeUtils;
use qtism\runtime\common\OrderedContainer;
use qtism\data\expressions\operators\Repeat;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The RepeatProcessor class aims at processing Repeat operators.
 * 
 * From IMS QTI:
 * 
 * The repeat operator takes 1 or more sub-expressions, all of which must have either 
 * single or ordered cardinality and the same baseType.
 * 
 * The result is an ordered container having the same baseType as its sub-expressions.
 * The container is filled sequentially by evaluating each sub-expression in turn and 
 * adding the resulting single values to the container, iterating this process 
 * numberRepeats times in total. If numberRepeats refers to a variable whose value 
 * is less than 1, the value of the whole expression is NULL.
 * 
 * Any sub-expressions evaluating to NULL are ignored. If all sub-expressions are 
 * NULL then the result is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RepeatProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Repeat) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The RepeatProcessor class only processes Repeat QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Repeat operator.
	 * 
	 * Note: NULL values are simply ignored. If all sub-expressions are NULL, NULL is
	 * returned.
	 * 
	 * @return OrderedContainer An ordered container filled sequentially by evaluating each sub-expressions, repeated a 'numberRepeats' of times. NULL is returned if all sub-expressions are NULL or numberRepeats < 1.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		// get the value of numberRepeats
		$expression = $this->getExpression();
		$numberRepeats = $expression->getNumberRepeats();
		
		if (is_string($numberRepeats)) {
			// Variable reference found.
			$state = $this->getState();
			$varName = Utils::sanitizeVariableRef($numberRepeats);
			$varValue = $state[$varName];
			
			if (is_null($varValue) === true) {
				$msg = "The variable with name '${varName}' could not be resolved.";
				throw new ExpressionProcessingException($msg, $this);
			}
			else if (is_int($varValue) === false) {
				$msg = "The variable with name '${varName}' is not an integer value.";
				throw new ExpressionProcessingException($msg, $this);
			}
			
			$numberRepeats = $varValue;
		}
		
		if ($numberRepeats < 1) {
			return null;
		}
		
		$result = null;
		for ($i = 0; $i < $numberRepeats; $i++) {
			$refType = null;
			
			foreach ($operands as $operand) {
				
				// If null, ignore
				if (is_null($operand) || ($operand instanceof Container && $operand->isNull())) {
					continue;
				}
				
				// Check cardinality.
				if ($i === 0 && (!is_scalar($operand) && !$operand instanceof OrderedContainer)) {
					$msg = "The Repeat operator only accepts operands with a single or ordered cardinality.";
					throw new ExpressionProcessingException($msg, $this);
				}
				
				// Check baseType.
				$currentType = RuntimeUtils::inferBaseType($operand);
				if ($i === 0 && $refType !== null && $currentType !== $refType) {
					$msg = "The Repeat operator only accepts operands with the same baseType.";
					throw new ExpressionProcessingException($msg, $this);
				}
				else if (is_null($result)) {
					$refType = $currentType;
					$result = new OrderedContainer($refType);
				}
				
				// Okay we are good...
				if ($operand instanceof OrderedContainer) {
					foreach ($operand as $o) {
						$result[] = (gettype($o) === 'object') ? clone $o : $o;
					}
				}
				else {
					$result[] = (gettype($operand) === 'object') ? clone $operand : $operand;
				}
			}
		}
		
		if (isset($result) && $result->isNull() !== true) {
			return $result;
		}
		else {
			return null;
		}
	}
}