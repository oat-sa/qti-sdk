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
	
	/**
	 * The code to use when an operand has a not compliant baseType OR
	 * cardinality.
	 * 
	 * @var integer
	 */
	const WRONG_BASETYPE_OR_CARDINALITY = 102;
	
	/**
	 * The code to use when not enough operands are given to a processor.
	 * 
	 * @var integer
	 */
	const NOT_ENOUGH_OPERANDS = 103;
	
	/**
	 * The code to use when too much operands are given to a processor.
	 * 
	 * @var integer
	 */
	const TOO_MUCH_OPERANDS = 104;
}