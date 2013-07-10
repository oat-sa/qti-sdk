<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The not operator takes a single sub-expression with a base-type of boolean 
 * and single cardinality. The result is a single boolean with a value obtained 
 * by the logical negation of the sub-expression's value. If the sub-expression 
 * is NULL then the not operator also results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Not extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::SINGLE), array(OperatorBaseType::BOOLEAN));
	}
	
	public function getQTIClassName() {
		return 'not';
	}
}