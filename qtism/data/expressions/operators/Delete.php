<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The delete operator takes two sub-expressions which must both have the same 
 * base-type. The first sub-expression must have single cardinality and the 
 * second must be a multiple or ordered container. The result is a new container
 * derived from the second sub-expression with all instances of the first 
 * sub-expression removed. For example, when applied to A and {B,A,C,A} the 
 * result is the container {B,C}. If either sub-expression is NULL the result 
 * of the operator is NULL.
 * 
 * The restrictions that apply to the member operator also apply to the 
 * delete operator.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Delete extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE, OperatorCardinality::MULTIPLE, OperatorCardinality::ORDERED), array(OperatorBaseType::SAME));
	}
	
	public function getQtiClassName() {
		return 'delete';
	}
}