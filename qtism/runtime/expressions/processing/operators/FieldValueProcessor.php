<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\FieldValue;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The FieldValueProcessor class aims at processing FieldValue expressions.
 * 
 * From IMS QTI:
 * 
 * The field-value operator takes a sub-expression with a record container value. 
 * The result is the value of the field with the specified fieldIdentifier. If there 
 * is no field with that identifier then the result of the operator is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class FieldValueProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof FieldValue) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The FieldValueProcessor class only processes FieldValue QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the FieldValue object.
	 * 
	 * @return mixed|null A QTI Runtime compliant value or null if there is no field with that identifier.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->exclusivelyRecord() === false) {
			$msg = "The FieldValue operator only accepts operands with a cardinality of record.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		$fieldIdentifier = $this->getExpression()->getFieldIdentifier();
		return $operands[0][$fieldIdentifier];
	}
}