<?php

namespace qtism\runtime\expressions\processing;

use qtism\common\enums\Cardinality;
use qtism\runtime\common\Utils as CommonUtils;
use qtism\data\expressions\operators\Member;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The MemberProcessor class aims at processing Member operators.
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
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->sameBaseType() === false) {
			$msg = "The Member operator only accepts values with the same baseType.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$operand1 = $operands[0];
		$operand2 = $operands[1];
		
		// The first expression must have single cardinality.
		if (CommonUtils::inferCardinality($operand1) !== Cardinality::SINGLE) {
			$msg = "The first operand of the Member operator must have a single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		// The second expression must have multiple or ordered cardinality.
		$cardinality = CommonUtils::inferCardinality($operand2);
		if ($cardinality !== Cardinality::MULTIPLE && $cardinality !== Cardinality::ORDERED) {
			$msg = "The second operand of the Member operator must have a multiple or ordered cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		return $operand2->contains($operand1);
	}
}