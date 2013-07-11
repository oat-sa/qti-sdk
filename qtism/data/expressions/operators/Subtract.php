<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The subtract operator takes 2 sub-expressions which all have single cardinality
 * and numerical base-types. The result is a single float or, if both sub-expressions
 * are of integer type, a single integer that corresponds to the first value minus
 * the second. If either of the sub-expressions is NULL then the operator results
 * in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Subtract extends Operator {
	
	public function __contruct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));
	}
	
	public function getQtiClassName() {
		return 'subtract';
	}
}