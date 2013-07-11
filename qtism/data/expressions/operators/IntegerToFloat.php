<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The round operator takes a single sub-expression which must have single cardinality
 * and a numerical base-type. The result is a value of base-type integer formed 
 * by rounding the value of the sub-expression. The result is the integer n for 
 * all input values in the range [n-0.5,n+0.5). In other words, 6.8 and 6.5 both 
 * round up to 7, 6.49 rounds down to 6 and -6.5 rounds up to -6. If the 
 * sub-expression is NULL then the operator results in NULL. If the 
 * sub-expression is NaN, then the result is NULL. If the sub-expression 
 * is INF, then the result is INF. If the sub-expression is -INF, then 
 * the result is -INF.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IntegerToFloat extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER));
	}
	
	public function getQtiClassName() {
		return 'integerToFloat';
	}
}