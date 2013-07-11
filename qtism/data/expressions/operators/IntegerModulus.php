<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The integer modulus operator takes 2 sub-expressions which both have single 
 * cardinality and base-type integer. The result is the single integer that 
 * corresponds to the remainder when the first expression (x) is divided by 
 * the second expression (y). If z is the result of the corresponding 
 * integerDivide operator then the result is x-z*y. If y is 0, or if either 
 * of the sub-expressions is NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IntegerModulus extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER));
	}
	
	public function getQtiClassName() {
		return 'integerModulus';
	}
}