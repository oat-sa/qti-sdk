<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The min operator takes 1 or more sub-expressions which all have numerical 
 * base-types and may have single, multiple or ordered cardinality. The result 
 * is a single float, or, if all sub-expressions are of integer type, a single 
 * integer, equal in value to the smallest of the argument values, i.e. the 
 * result is the argument closest to negative infinity. If the arguments have 
 * the same value, the result is that same value. If any of the sub-expressions 
 * is NULL, the result is NULL. If any of the sub-expressions is not a numerical 
 * value, then the result is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Min extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, -1, array(OperatorCardinality::SINGLE, OperatorCardinality::MULTIPLE, OperatorCardinality::ORDERED), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));
	}
	
	public function getQtiClassName() {
		return 'min';
	}
}