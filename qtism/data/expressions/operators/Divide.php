<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The divide operator takes 2 sub-expressions which both have single cardinality
 * and numerical base-types. The result is a single float that corresponds to the
 * first expression divided by the second expression. If either of the sub-expressions
 * is NULL then the operator results in NULL.
 * 
 * Item authors should make every effort to ensure that the value of the second
 * expression is never 0, however, if it is zero or the resulting value is
 * outside the value set defined by float (not including positive and negative
 * infinity) then the operator should result in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Divide extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));
	}
	
	public function getQtiClassName() {
		return 'divide';
	}
}