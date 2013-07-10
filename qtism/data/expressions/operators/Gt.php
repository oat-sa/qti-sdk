<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The gt operator takes two sub-expressions which must both have single 
 * cardinality and have a numerical base-type. The result is a single 
 * boolean with a value of true if the first expression is numerically 
 * greater than the second and false if it is less than or equal to the 
 * second. If either sub-expression is NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Gt extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));
	}
	
	public function getQTIClassName() {
		return 'gt';
	}
}