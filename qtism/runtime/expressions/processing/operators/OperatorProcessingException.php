<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\runtime\expressions\processing\ExpressionProcessingException;

/**
 * The OperatorProcessingException class represents an exception to be thrown
 * when an error occurs while processing an Operator at runtime.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OperatorProcessingException extends ExpressionProcessingException {
	
	/**
	 * The code to use when an operand with a not compliant cardinality
	 * is processed by the operator.
	 * 
	 * @var integer
	 */
	const WRONG_CARDINALITY = 100;
	
	/**
	 * The code to use when an operand with a not compliant baseType is
	 * processed by the operator.
	 * 
	 * @var integer
	 */
	const WRONG_BASETYPE = 101;
}