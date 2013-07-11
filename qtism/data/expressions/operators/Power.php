<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The power operator takes 2 sub-expression which both have single cardinality
 * and numerical base-types. The result is a single float that corresponds to the
 * first expression raised to the power of the second. If either or the 
 * sub-expressions is NULL then the operator results in NULL.
 * 
 * If the resulting value is outside the value set defined by 
 * float (not including positive and negative infinity) then the operator
 * shall result in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Power extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));
	}
	
	public function getQtiClassName() {
		return 'power';
	}
}