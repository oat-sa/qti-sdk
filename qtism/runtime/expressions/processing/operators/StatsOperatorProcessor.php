<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\Statistics;
use qtism\runtime\expressions\processing\operators\Utils as OperatorsUtils;
use qtism\data\expressions\operators\StatsOperator;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The StatsOperatorProcessor class aims at processing StatsOperator operators.
 * 
 * Please note that the Bessel's correction is applied for sampleVariance and sampleSD.
 * 
 * From IMS QTI:
 * 
 * The statsOperator operator takes 1 sub-expression which is a container of multiple 
 * or ordered cardinality and has a numerical base-type. The result is a single float. 
 * If the sub-expression or any value contained therein is NULL, the result is NULL. If 
 * any value contained in the sub-expression is not a numerical value, then the result 
 * is NULL.
 * 
 * * mean: The arithmetic mean of the argument, which must be a container of numerical base type, which contains a sample of observations.
 * 
 * * sampleVariance: The variance of the argument, which must be a container of numerical base type, with containerSize greater than 1, containing a sample of observations.
 * 
 * * sampleSD: The standard deviation of the argument, which must be a container of numerical base type, with containerSize greater than 1, containing a sample of observations.
 * 
 * * popVariance: The variance of the argument, which must be a container of numerical base type with containerSize greater than 1.
 * 
 * * popSD: The standard deviation of the argument, which must be a container of numerical base type with containerSize greater than 1.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @link http://en.wikipedia.org/wiki/Variance#Sample_variance
 *
 */
class StatsOperatorProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof StatsOperator) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The StatsOperatorProcessor class only processes StatsOperator QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the StatsOperator.
	 * 
	 * @return float A single float or NULL if the sub-expression or any value contained therein is NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelyMultipleOrOrdered() === false) {
			$msg = "The StatsOperator operator only accepts operands with a multiple or ordered cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		if ($operands->exclusivelyNumeric() === false) {
			$msg = "The StatsOperator operator only accepts operands with a multiple or ordered cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$qtiFuncName = Statistics::getNameByConstant($this->getExpression()->getName());
		$methodName = 'process' . ucfirst($qtiFuncName);
		
		return call_user_func_array(array($this, $methodName), array());
	}
	
	protected function processMean() {
		$operands = $this->getOperands();
		$operand = $operands[0];
		
		$result = OperatorsUtils::mean($operand->getArrayCopy());
		return ($result !== false) ? floatval($result) : null;
	}
	
	protected function processSampleVariance() {
		$operands = $this->getOperands();
		$operand = $operands[0];
		
		$result = OperatorsUtils::variance($operand->getArrayCopy(), true);
		return ($result !== false) ? floatval($result) : null;
	}
	
	protected function processSampleSD() {
		$operands = $this->getOperands();
		$operand = $operands[0];
		
		$result = OperatorsUtils::standardDeviation($operand->getArrayCopy(), true);
		return ($result !== false) ? floatval($result) : null;
	}
	
	protected function processPopVariance() {
		$operands = $this->getOperands();
		$operand = $operands[0];
		
		$result = OperatorsUtils::variance($operand->getArrayCopy(), false);
		return ($result !== false) ? floatval($result) : null;
	}
	
	protected function processPopSD() {
		$operands = $this->getOperands();
		$operand = $operands[0];
		
		$result = OperatorsUtils::standardDeviation($operand->getArrayCopy(), false);
		return ($result !== false) ? floatval($result) : null;
	}
}