<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * Please note that this class represents the QTI 'or' class.
 * We cannot use the 'Or' class name because it is a reserved word
 * in PHP.
 * 
 * From IMS QTI:
 * 
 * The or operator takes one or more sub-expressions each with a base-type of 
 * boolean and single cardinality. The result is a single boolean which is 
 * true if any of the sub-expressions are true and false if all of them are 
 * false. If one or more sub-expressions are NULL and all the others are 
 * false then the operator also results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OrOperator extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, -1, array(OperatorCardinality::SINGLE), array(OperatorBaseType::BOOLEAN));
	}
	
	public function getQTIClassName() {
		return 'or';
	}
}