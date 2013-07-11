<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The random operator takes a sub-expression with a multiple or ordered 
 * container value and any base-type. The result is a single value randomly
 * selected from the container. The result has the same base-type as the 
 * sub-expression but single cardinality. If the sub-expression is NULL 
 * then the result is also NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Random extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::MULTIPLE, OperatorCardinality::ORDERED), array(OperatorBaseType::ANY));
	}
	
	public function getQtiClassName() {
		return 'random';
	}
}