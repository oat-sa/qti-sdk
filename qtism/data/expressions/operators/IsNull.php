<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The isNull operator takes a sub-expression with any base-type and cardinality.
 * The result is a single boolean with a value of true if the sub-expression is 
 * NULL and false otherwise. Note that empty containers and empty strings are 
 * both treated as NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class IsNull extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::ANY), array(OperatorBaseType::ANY));
	}
	
	public function getQtiClassName() {
		return 'isNull';
	}
}