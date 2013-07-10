<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The product operator takes 1 or more sub-expressions which all have numerical 
 * base-types and may have single, multiple or ordered cardinality. The result 
 * is a single float or, if all sub-expressions are of integer type, a single 
 * integer that corresponds to the product of the numerical values of the 
 * sub-expressions. If any of the sub-expressions are NULL then the operator 
 * results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Product extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, -1, array(OperatorCardinality::SINGLE, OperatorCardinality::MULTIPLE, OperatorCardinality::ORDERED), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));
	}
	
	public function getQTIClassName() {
		return 'product';
	}
}