<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The integer divide operator takes 2 sub-expressions which both have single
 * cardinality and base-type integer. The result is the single integer that 
 * corresponds to the first expression (x) divided by the second expression (y)
 * rounded down to the greatest integer (i) such that i<=(x/y). If y is 0, or if
 * either of the sub-expressions is NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IntegerDivide extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER));
	}
	
	public function getQtiClassName() {
		return 'integerDivide';
	}
}