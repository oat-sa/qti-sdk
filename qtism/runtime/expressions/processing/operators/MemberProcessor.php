<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\common\enums\Cardinality;
use qtism\runtime\common\Utils as CommonUtils;
use qtism\data\expressions\operators\Member;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The MemberProcessor class aims at processing Member operators.
 * 
 * From IMS QTI:
 * 
 * The member operator takes two sub-expressions which must both have the same base-type. The first sub-expression must
 * have single cardinality and the second must be a multiple or ordered container. The result is a single boolean with a
 * value of true if the value given by the first sub-expression is in the container defined by the second sub-expression.
 * If either sub-expression is NULL then the result of the operator is NULL.
 * 
 * The member operator should not be used on sub-expressions with a base-type of float because of the poorly defined comparison of values.
 * It must not be used on sub-expressions with a base-type of duration.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MemberProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Member) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The MemberProcessor class only processes Member QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the Member operator.
	 * 
	 * @return boolean Whether the first operand is contained by the second one as a boolean value, or NULL if any of the sub-expressions are NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->sameBaseType() === false) {
			$msg = "The Member operator only accepts values with the same baseType.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
		}
		
		$operand1 = $operands[0];
		$operand2 = $operands[1];
		
		// The first expression must have single cardinality.
		if (CommonUtils::inferCardinality($operand1) !== Cardinality::SINGLE) {
			$msg = "The first operand of the Member operator must have a single cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		// The second expression must have multiple or ordered cardinality.
		$cardinality = CommonUtils::inferCardinality($operand2);
		if ($cardinality !== Cardinality::MULTIPLE && $cardinality !== Cardinality::ORDERED) {
			$msg = "The second operand of the Member operator must have a multiple or ordered cardinality.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
		}
		
		return $operand2->contains($operand1);
	}
}